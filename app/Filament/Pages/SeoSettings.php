<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\Seo;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class SeoSettings extends Page
{
    protected static ?string $title = 'SEO Settings';
    protected static ?string $navigationLabel = 'SEO Settings';
    protected static string | UnitEnum | null $navigationGroup = 'SEO';
    protected static ?int $navigationSort = 2;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedAdjustmentsVertical;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect(Seo::KEYS)
            ->mapWithKeys(fn (string $key) => [$key => SettingsService::get($key)])
            ->put('seo_hide_site', app(Seo::class)->isHidden())
            ->all());
    }

    public function form(Schema $schema): Schema
    {
        $verificationHelp = 'Paste the code, or the whole <meta> tag.';

        return $schema
            ->components([
                Section::make('Titles')
                    ->description('Every page title follows this pattern unless the page has its own SEO title (Page SEO).')
                    ->schema([
                        TextInput::make('seo_site_name')
                            ->label('Site name')
                            ->required()
                            ->maxLength(150)
                            ->live(debounce: 400)
                            ->helperText('Fills %sitename%. Also shown as the site name when pages are shared.'),
                        Select::make('seo_separator')
                            ->label('Separator')
                            ->options(Seo::SEPARATORS)
                            ->required()
                            ->live()
                            ->helperText('Fills %sep%.'),
                        TextInput::make('seo_title_pattern')
                            ->label('Title pattern')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 400)
                            ->columnSpanFull()
                            ->helperText(fn (Get $get): string => 'Variables: %title% (page name), %sep% (separator), %sitename% (site name). Web Hosting page: "'
                                . trim(preg_replace('/\s+/u', ' ', strtr((string) $get('seo_title_pattern'), [
                                    '%title%' => 'Web Hosting',
                                    '%sep%' => (string) $get('seo_separator'),
                                    '%sitename%' => (string) $get('seo_site_name'),
                                ]))) . '"'),
                    ])
                    ->columns(2),

                Section::make('Search engine visibility')
                    ->schema([
                        Toggle::make('seo_hide_site')
                            ->label('Hide this site from search engines')
                            ->helperText('For the demo or staging site only. Adds "noindex" to every page and turns off Google Analytics and Tag Manager. Keep this OFF on hostorio.com, or the site will drop out of Google.'),
                    ]),

                Section::make('Images')
                    ->schema([
                        FileUpload::make('seo_default_image')
                            ->label('Default share image')
                            ->image()
                            ->disk('public')
                            ->directory('seo')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->helperText('Shown when a page is shared on Facebook, X, LinkedIn or WhatsApp and has no image of its own. 1200 × 630 pixels works best.'),
                        FileUpload::make('seo_favicon')
                            ->label('Site icon (favicon)')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'])
                            ->disk('public')
                            ->directory('seo')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->helperText('The small icon in browser tabs and Google results. A square PNG of at least 512 × 512 pixels.'),
                    ])
                    ->columns(2),

                Section::make('Webmaster tools verification')
                    ->description('Proves to search engines that you own the site. Leave empty if you verified another way (for example a DNS record).')
                    ->schema([
                        TextInput::make('seo_verify_google')->label('Google Search Console')->maxLength(500)->helperText($verificationHelp),
                        TextInput::make('seo_verify_bing')->label('Bing Webmaster Tools')->maxLength(500)->helperText($verificationHelp),
                        TextInput::make('seo_verify_yandex')->label('Yandex')->maxLength(500)->helperText($verificationHelp),
                        TextInput::make('seo_verify_baidu')->label('Baidu')->maxLength(500)->helperText($verificationHelp),
                        TextInput::make('seo_verify_pinterest')->label('Pinterest')->maxLength(500)->helperText($verificationHelp),
                        TextInput::make('seo_verify_facebook')->label('Facebook domain verification')->maxLength(500)->helperText($verificationHelp),
                    ])
                    ->columns(2),

                Section::make('Analytics')
                    ->schema([
                        TextInput::make('seo_ga4_id')
                            ->label('Google Analytics 4 measurement ID')
                            ->placeholder('G-XXXXXXXXXX')
                            ->regex('/^\s*[Gg]-[A-Za-z0-9]+\s*$/')
                            ->validationMessages(['regex' => 'A measurement ID looks like G-PJSW407GJS.'])
                            ->maxLength(30),
                        TextInput::make('seo_gtm_id')
                            ->label('Google Tag Manager container ID (optional)')
                            ->placeholder('GTM-XXXXXXX')
                            ->regex('/^\s*[Gg][Tt][Mm]-[A-Za-z0-9]+\s*$/')
                            ->validationMessages(['regex' => 'A container ID looks like GTM-ABC1234.'])
                            ->maxLength(30)
                            ->helperText('Only if you use Tag Manager. If Tag Manager already loads Analytics, leave the Analytics ID empty so visits are not counted twice.'),
                    ])
                    ->columns(2),

                Section::make('Custom code')
                    ->description('For other tools, such as the chat widget or Facebook Pixel. The code is added to every page exactly as typed, so only paste code from a source you trust.')
                    ->schema([
                        Textarea::make('seo_head_code')
                            ->label('Code in <head>')
                            ->rows(5)
                            ->extraInputAttributes(['style' => 'font-family: ui-monospace, monospace; font-size: 13px;']),
                        Textarea::make('seo_footer_code')
                            ->label('Code before </body>')
                            ->rows(5)
                            ->extraInputAttributes(['style' => 'font-family: ui-monospace, monospace; font-size: 13px;']),
                    ]),
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
                                ->label('Save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ])->key('form-actions'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (Seo::KEYS as $key) {
            $value = $state[$key] ?? null;

            $value = match (true) {
                $key === 'seo_hide_site' => (bool) $value,
                in_array($key, ['seo_head_code', 'seo_footer_code'], true) => trim((string) $value),
                array_key_exists($key, Seo::VERIFICATION) => Seo::verificationCode($value),
                in_array($key, ['seo_ga4_id', 'seo_gtm_id'], true) => strtoupper(trim((string) $value)),
                default => is_string($value) ? trim($value) : $value,
            };

            Setting::set($key, $value);
        }

        $notification = Notification::make()->title('Saved');

        if ((bool) $state['seo_hide_site']) {
            $notification->warning()->body('The site is hidden from search engines. Turn this off on hostorio.com.')->persistent();
        } else {
            $notification->success()->body('The website uses the new settings now.');
        }

        $notification->send();
    }
}
