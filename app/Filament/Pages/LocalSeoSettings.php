<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\LocalSeo;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
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
use Illuminate\Support\HtmlString;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class LocalSeoSettings extends Page
{
    protected static ?string $title = 'Local SEO';
    protected static ?string $navigationLabel = 'Local SEO';
    protected static ?string $slug = 'local-seo';
    protected static string | UnitEnum | null $navigationGroup = 'SEO';
    protected static ?int $navigationSort = 3;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getSubheading(): ?string
    {
        return 'Business details Google uses for the knowledge panel and local results.';
    }

    public function mount(): void
    {
        $local = app(LocalSeo::class);

        $this->form->fill([
            'biz_type' => $local->type(),
            'biz_description' => SettingsService::get('biz_description') ?: LocalSeo::DEFAULT_DESCRIPTION,
            'biz_logo' => SettingsService::get('biz_logo'),
            'biz_price_range' => $local->priceRange(),
            'biz_area_served' => $local->areaServed(),
            'biz_languages' => $local->languages(),
            'biz_hours_247' => $local->isOpen247(),
            'biz_hours' => SettingsService::get('biz_hours') ?: [['days' => array_slice(LocalSeo::DAYS, 0, 5), 'opens' => '09:00', 'closes' => '21:00']],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business')
                    ->description(new HtmlString('Name, phone, email, address, map location and social links come from <a href="' . e(ContactAddress::getUrl()) . '" style="text-decoration:underline;">Contact &amp; Address</a>.'))
                    ->schema([
                        Select::make('biz_type')
                            ->label('Business type')
                            ->options(LocalSeo::TYPES)
                            ->required()
                            ->live()
                            ->helperText('Choose a type with an office if customers can visit you or you want opening hours in Google. That matches your Google Business Profile.'),
                        TextInput::make('biz_price_range')
                            ->label('Price range')
                            ->maxLength(20)
                            ->placeholder('৳৳')
                            ->visible(fn (Get $get): bool => $this->isLocal($get))
                            ->helperText('৳ (cheap) to ৳৳৳৳ (expensive).'),
                        Textarea::make('biz_description')
                            ->label('Business description')
                            ->rows(3)
                            ->maxLength(500)
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('biz_logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('seo')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Leave empty to use the logo in the site header. A PNG of at least 112 × 112 pixels on a plain background.'),
                        TextInput::make('biz_area_served')
                            ->label('Area served')
                            ->maxLength(100)
                            ->placeholder('BD')
                            ->helperText('A country code (BD) or a place name.'),
                        TagsInput::make('biz_languages')
                            ->label('Support languages')
                            ->placeholder('Add a language')
                            ->splitKeys(['Tab', ','])
                            ->helperText('Type a language and press Enter.'),
                    ])
                    ->columns(2),

                Section::make('Opening hours')
                    ->visible(fn (Get $get): bool => $this->isLocal($get))
                    ->schema([
                        Toggle::make('biz_hours_247')
                            ->label('Open 24 hours, 7 days a week')
                            ->live()
                            ->helperText('Matches the current site. Switch off to enter office hours instead.'),
                        Repeater::make('biz_hours')
                            ->label('Hours')
                            ->hiddenLabel()
                            ->visible(fn (Get $get): bool => ! $get('biz_hours_247'))
                            ->schema([
                                CheckboxList::make('days')
                                    ->options(array_combine(LocalSeo::DAYS, LocalSeo::DAYS))
                                    ->columns(4)
                                    ->required()
                                    ->columnSpanFull(),
                                TimePicker::make('opens')->label('Opens')->seconds(false)->required(),
                                TimePicker::make('closes')->label('Closes')->seconds(false)->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add hours')
                            ->defaultItems(1)
                            ->helperText('Add a row for each set of days with the same hours, e.g. Saturday–Thursday 09:00–21:00.'),
                    ]),
            ])
            ->statePath('data');
    }

    private function isLocal(Get $get): bool
    {
        return in_array($get('biz_type'), ['LocalBusiness', 'ProfessionalService', 'Store'], true);
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
                            Action::make('test')
                                ->label('Test in Google')
                                ->color('gray')
                                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                                ->url('https://search.google.com/test/rich-results?url=' . urlencode(hostorio_url()))
                                ->openUrlInNewTab(),
                        ])->key('form-actions'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('biz_type', $state['biz_type']);
        Setting::set('biz_description', trim((string) $state['biz_description']));
        Setting::set('biz_logo', $state['biz_logo'] ?? null);
        Setting::set('biz_area_served', trim((string) ($state['biz_area_served'] ?? '')));
        Setting::set('biz_languages', array_values(array_filter(array_map('trim', $state['biz_languages'] ?? []))));

        // Hidden for online-only types: keep what was there.
        if (array_key_exists('biz_price_range', $state)) {
            Setting::set('biz_price_range', trim((string) $state['biz_price_range']));
        }

        if (array_key_exists('biz_hours_247', $state)) {
            Setting::set('biz_hours_247', (bool) $state['biz_hours_247']);
        }

        if (array_key_exists('biz_hours', $state)) {
            Setting::set('biz_hours', array_values(array_map(fn (array $row) => [
                'days' => array_values($row['days'] ?? []),
                'opens' => substr((string) ($row['opens'] ?? ''), 0, 5),
                'closes' => substr((string) ($row['closes'] ?? ''), 0, 5),
            ], $state['biz_hours'] ?? [])));
        }

        Notification::make()->success()->title('Saved')->body('Search engines see the new details the next time they visit.')->send();
    }
}
