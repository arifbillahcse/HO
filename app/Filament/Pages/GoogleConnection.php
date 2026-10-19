<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\GoogleReports;
use App\Services\SettingsService;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Throwable;

/**
 * @property-read Schema $form
 */
class GoogleConnection extends Page
{
    protected static ?string $title = 'Google Connection';
    protected static ?string $navigationLabel = 'Google Connection';
    protected static ?int $navigationSort = 91;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedChartBar;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getSubheading(): ?string
    {
        return 'Shows Google Search Console and Google Analytics numbers on the dashboard.';
    }

    public function mount(): void
    {
        $this->form->fill([
            'gsc_property' => SettingsService::get('google_gsc_property'),
            'ga4_property' => SettingsService::get('google_ga4_property'),
            'cache_minutes' => SettingsService::get('google_cache_minutes') ?: GoogleReports::DEFAULT_CACHE_MINUTES,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $email = app(GoogleReports::class)->clientEmail();

        return $schema
            ->components([
                Section::make('One-time setup in Google')
                    ->collapsible()
                    ->collapsed($email !== null)
                    ->schema([
                        Text::make(new HtmlString(<<<'HTML'
                            <ol style="list-style:decimal;padding-left:20px;line-height:1.7;">
                                <li>Open <a href="https://console.cloud.google.com/" target="_blank" rel="noopener" style="text-decoration:underline;">Google Cloud Console</a> and create a project (any name, e.g. "Hostorio website").</li>
                                <li>APIs &amp; Services → Library: enable <strong>Google Search Console API</strong> and <strong>Google Analytics Data API</strong>.</li>
                                <li>IAM &amp; Admin → Service accounts → <strong>Create service account</strong> (no roles needed). Open it → Keys → Add key → Create new key → <strong>JSON</strong>. A file downloads.</li>
                                <li>Search Console → Settings → Users and permissions → <strong>Add user</strong>: the service account's email (it ends in .iam.gserviceaccount.com), permission <strong>Restricted</strong>.</li>
                                <li>Google Analytics → Admin → Property access management → <strong>+</strong>: the same email, role <strong>Viewer</strong>.</li>
                                <li>Paste the downloaded file's contents below and click <strong>Save and test</strong>.</li>
                            </ol>
                            HTML)),
                    ]),
                Section::make('Connection')
                    ->schema([
                        Textarea::make('service_account')
                            ->label('Service account key (JSON)')
                            ->rows(6)
                            ->placeholder('{ "type": "service_account", "project_id": "...", "private_key": "-----BEGIN PRIVATE KEY-----...", "client_email": "...@....iam.gserviceaccount.com", ... }')
                            ->required($email === null)
                            ->extraInputAttributes(['style' => 'font-family: ui-monospace, monospace; font-size: 12px;', 'autocomplete' => 'off', 'spellcheck' => 'false'])
                            ->rule(fn (): Closure => function (string $attribute, $value, Closure $fail): void {
                                if (filled($value)) {
                                    try {
                                        GoogleReports::parseServiceAccount($value);
                                    } catch (Throwable $e) {
                                        $fail($e->getMessage());
                                    }
                                }
                            })
                            ->helperText($email
                                ? new HtmlString('Saved, encrypted: <strong>' . e($email) . '</strong>. Paste a new key only to replace it. Give this email access in Search Console and Analytics.')
                                : 'Stored encrypted. Only the email and private key are kept.'),
                        TextInput::make('gsc_property')
                            ->label('Search Console property')
                            ->placeholder('sc-domain:hostorio.com')
                            ->maxLength(255)
                            ->helperText('As shown in Search Console\'s property menu: sc-domain:hostorio.com for a Domain property, or https://hostorio.com/ for a URL-prefix property. Leave empty to skip.'),
                        TextInput::make('ga4_property')
                            ->label('Google Analytics 4 property ID')
                            ->placeholder('412345678')
                            ->maxLength(20)
                            ->regex('/^\s*\d+\s*$/')
                            ->validationMessages(['regex' => 'The property ID is a number (GA4 → Admin → Property details). It is not the G-… measurement ID.'])
                            ->helperText('GA4 → Admin → Property details → Property ID (a number). Not the G-PJSW407GJS measurement ID. Leave empty to skip.'),
                        TextInput::make('cache_minutes')
                            ->label('Refresh numbers every (minutes)')
                            ->numeric()
                            ->required()
                            ->minValue(5)
                            ->maxValue(1440)
                            ->helperText('Google updates Search Console about once a day, so a few hours is plenty.'),
                    ])
                    ->columns(1),
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
                            Action::make('save')->label('Save and test')->submit('save')->keyBindings(['mod+s']),
                            Action::make('test')->label('Test connection')->color('gray')->action(fn () => $this->testConnection()),
                        ])->key('form-actions'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('disconnect')
                ->label('Disconnect')
                ->color('danger')
                ->outlined()
                ->requiresConfirmation()
                ->modalDescription('Remove the saved key? The dashboard stops showing Google numbers.')
                ->visible(fn (): bool => app(GoogleReports::class)->clientEmail() !== null)
                ->action(function (): void {
                    Setting::set('google_service_account', null);
                    GoogleReports::flush();
                    Notification::make()->success()->title('Disconnected')->send();
                    $this->redirect(static::getUrl());
                }),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        if (filled($state['service_account'] ?? null)) {
            GoogleReports::storeServiceAccount($state['service_account']);
        }

        Setting::set('google_gsc_property', trim((string) ($state['gsc_property'] ?? '')));
        Setting::set('google_ga4_property', preg_replace('/\D/', '', (string) ($state['ga4_property'] ?? '')));
        Setting::set('google_cache_minutes', (int) $state['cache_minutes']);
        GoogleReports::flush();

        $this->form->fill(array_merge($state, ['service_account' => null]));
        $this->testConnection(saved: true);
    }

    public function testConnection(bool $saved = false): void
    {
        $state = $saved ? null : $this->form->getState();
        $reports = $saved ? app(GoogleReports::class) : $this->unsavedReports($state);

        if ($reports->serviceAccount() === null) {
            Notification::make()->warning()->title('No key yet')->body('Paste the service account key first.')->send();

            return;
        }

        $results = [];
        $failed = false;
        $range = GoogleReports::range(28);

        if ($reports->searchConsoleProperty() !== '') {
            try {
                $rows = $reports->searchQuery($range['start'], $range['end'], ['date']);
                $results[] = 'Search Console: ' . number_format(collect($rows)->sum('clicks')) . ' clicks in the last 28 days.';
            } catch (Throwable $e) {
                $failed = true;
                $results[] = $e->getMessage();
            }
        }

        if ($reports->analyticsProperty() !== '') {
            try {
                $report = $reports->analyticsReport([
                    'dateRanges' => [['startDate' => $range['start'], 'endDate' => $range['end']]],
                    'metrics' => [['name' => 'activeUsers']],
                ]);
                $results[] = 'Analytics: ' . number_format((float) ($report['rows'][0]['metricValues'][0]['value'] ?? 0)) . ' users in the last 28 days.';
            } catch (Throwable $e) {
                $failed = true;
                $results[] = $e->getMessage();
            }
        }

        if ($results === []) {
            $results[] = 'Add a Search Console property or an Analytics property ID.';
            $failed = true;
        }

        Notification::make()
            ->title($failed ? ($saved ? 'Saved, but the connection failed' : 'Connection failed') : ($saved ? 'Saved and connected' : 'Connection works'))
            ->body(implode(' ', array_unique($results)))
            ->{$failed ? 'danger' : 'success'}()
            ->persistent($failed)
            ->send();
    }

    private function unsavedReports(array $state): GoogleReports
    {
        $account = null;

        if (filled($state['service_account'] ?? null)) {
            $parsed = GoogleReports::parseServiceAccount($state['service_account']);
            $account = ['client_email' => $parsed['client_email'], 'private_key' => $parsed['private_key']];
        }

        return GoogleReports::withSettings([
            'service_account' => $account ?? app(GoogleReports::class)->serviceAccount(),
            'gsc_property' => trim((string) ($state['gsc_property'] ?? '')),
            'ga4_property' => (string) ($state['ga4_property'] ?? ''),
        ]);
    }
}
