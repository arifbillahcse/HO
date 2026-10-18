<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SettingsService;
use App\Services\WhmcsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;

/**
 * @property-read Schema $form
 */
class WhmcsConnection extends Page
{
    protected static ?string $title = 'WHMCS Connection';
    protected static ?string $navigationLabel = 'WHMCS Connection';
    protected static ?int $navigationSort = 90;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedLink;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'url' => SettingsService::get('whmcs_url'),
            'api_identifier' => SettingsService::get('whmcs_api_identifier'),
            'currency_id' => SettingsService::get('whmcs_currency_id'),
            'cache_minutes' => SettingsService::get('whmcs_cache_minutes') ?: WhmcsService::DEFAULT_CACHE_MINUTES,
            'hero_tlds' => app(WhmcsService::class)->heroTlds(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $hasSecret = WhmcsService::hasStoredSecret();

        return $schema
            ->components([
                Section::make('API connection')
                    ->description('Create an API credential in WHMCS (Configuration > System Settings > API Credentials) with permission for "GetTLDPricing", and allow this server\'s IP address. Prices are fetched on the server, so the secret never reaches visitors.')
                    ->schema([
                        TextInput::make('url')
                            ->label('WHMCS URL')
                            ->placeholder('https://my.hostorio.com')
                            ->helperText('The address of your WHMCS install, without /includes/api.php.')
                            ->required()
                            ->maxLength(255)
                            ->url()
                            ->startsWith(['https://'])
                            ->validationMessages(['starts_with' => 'The WHMCS URL must start with https://']),
                        TextInput::make('api_identifier')
                            ->label('API identifier')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('api_secret')
                            ->label('API secret')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(! $hasSecret)
                            ->helperText($hasSecret ? 'A secret is saved. Leave blank to keep it.' : 'Stored encrypted.')
                            ->maxLength(255),
                        TextInput::make('api_access_key')
                            ->label('API access key (optional)')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->helperText('Only needed if WHMCS restricts API access by IP and you cannot allow this server\'s IP instead.')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Homepage prices')
                    ->schema([
                        TagsInput::make('hero_tlds')
                            ->label('Extensions shown in the hero')
                            ->placeholder('.com')
                            ->helperText('Type an extension and press Enter. They appear in this order. Any extension WHMCS has no price for is skipped.')
                            ->splitKeys(['Tab', ',', ' '])
                            ->required()
                            ->nestedRecursiveRules(['regex:/^\.?[a-zA-Z0-9][a-zA-Z0-9.\-]*$/']),
                        TextInput::make('currency_id')
                            ->label('WHMCS currency ID (optional)')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Leave empty to use the WHMCS default currency.'),
                        TextInput::make('cache_minutes')
                            ->label('Refresh prices every (minutes)')
                            ->numeric()
                            ->required()
                            ->minValue(5)
                            ->maxValue(10080)
                            ->helperText('Prices are cached so the homepage stays fast. If WHMCS is unreachable, the last good prices are kept.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save and sync')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                            Action::make('test')
                                ->label('Test connection')
                                ->color('gray')
                                ->action(fn () => $this->testConnection()),
                        ])->key('form-actions'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('whmcs_url', rtrim(trim($state['url']), '/'));
        Setting::set('whmcs_api_identifier', trim($state['api_identifier']));
        Setting::set('whmcs_currency_id', filled($state['currency_id'] ?? null) ? (int) $state['currency_id'] : null);
        Setting::set('whmcs_cache_minutes', (int) $state['cache_minutes']);
        Setting::set('whmcs_hero_tlds', array_values(array_unique(array_map(WhmcsService::normalizeTld(...), $state['hero_tlds']))));

        if (filled($state['api_secret'] ?? null)) {
            WhmcsService::storeSecret('whmcs_api_secret', $state['api_secret']);
        }

        if (filled($state['api_access_key'] ?? null)) {
            WhmcsService::storeSecret('whmcs_api_access_key', $state['api_access_key']);
        }

        WhmcsService::flush();
        $this->form->fill(array_merge($state, ['api_secret' => null, 'api_access_key' => null]));

        try {
            $count = count(app(WhmcsService::class)->pricing()['tlds'] ?? []);

            if ($count === 0) {
                throw new \RuntimeException('WHMCS returned no domain prices.');
            }

            Notification::make()
                ->success()
                ->title('Saved and connected')
                ->body("Loaded prices for {$count} extensions. The homepage now shows live prices.")
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->warning()
                ->title('Saved, but the connection failed')
                ->body($e->getMessage() . ' The homepage keeps showing the built-in prices until this works.')
                ->persistent()
                ->send();
        }
    }

    public function testConnection(): void
    {
        $state = $this->form->getState();

        $service = WhmcsService::withCredentials([
            'url' => rtrim(trim($state['url']), '/'),
            'identifier' => trim($state['api_identifier']),
            'secret' => filled($state['api_secret'] ?? null) ? $state['api_secret'] : $this->storedSecret('whmcs_api_secret'),
            'access_key' => filled($state['api_access_key'] ?? null) ? $state['api_access_key'] : $this->storedSecret('whmcs_api_access_key'),
            'currency_id' => filled($state['currency_id'] ?? null) ? (int) $state['currency_id'] : null,
        ]);

        try {
            $pricing = $service->fetchPricing();
        } catch (Throwable $e) {
            Notification::make()->danger()->title('Connection failed')->body($e->getMessage())->persistent()->send();

            return;
        }

        $sample = collect($state['hero_tlds'])
            ->map(WhmcsService::normalizeTld(...))
            ->filter(fn ($tld) => isset($pricing['tlds'][$tld]))
            ->map(fn ($tld) => $tld . ' ' . WhmcsService::format($pricing['tlds'][$tld], $pricing['currency']) . '/yr')
            ->implode(', ');

        Notification::make()
            ->success()
            ->title('Connection works')
            ->body(count($pricing['tlds']) . ' extensions found' . ($pricing['currency']['code'] ? " ({$pricing['currency']['code']})" : '') . ($sample ? ". Your hero would show: {$sample}." : '.') . ' Click "Save and sync" to apply.')
            ->send();
    }

    private function storedSecret(string $key): ?string
    {
        $credentials = app(WhmcsService::class)->credentials();

        return match ($key) {
            'whmcs_api_secret' => $credentials['secret'] ?? null,
            default => $credentials['access_key'] ?? null,
        };
    }
}
