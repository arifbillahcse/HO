<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\BusinessInfo;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class ContactAddress extends Page
{
    protected static ?string $title = 'Contact & Address';
    protected static ?string $navigationLabel = 'Contact & Address';
    protected static ?int $navigationSort = 80;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedMapPin;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect(BusinessInfo::KEYS)
            ->mapWithKeys(fn (string $key) => [$key => SettingsService::get($key)])
            ->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact')
                    ->description('Shown in the header, footer, About and Terms pages, and given to search engines.')
                    ->schema([
                        TextInput::make('business_name')
                            ->label('Business name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('business_phone')
                            ->label('Phone')
                            ->tel()
                            ->required()
                            ->maxLength(30)
                            ->placeholder('+8801779440297')
                            ->helperText('Shown exactly as typed. Include the country code (+880) so the call link works.'),
                        TextInput::make('business_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('client_area_url')
                            ->label('Client area (WHMCS) address')
                            ->url()
                            ->required()
                            ->startsWith(['https://'])
                            ->validationMessages(['starts_with' => 'The address must start with https://'])
                            ->maxLength(255)
                            ->placeholder('https://my.hostorio.com/')
                            ->helperText('Used for the Help Center, support ticket, login and domain links.'),
                    ])
                    ->columns(2),
                Section::make('Address')
                    ->schema([
                        TextInput::make('business_address')
                            ->label('Street address')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('H-161, OABDA SOROK MATHBARIA')
                            ->columnSpanFull(),
                        TextInput::make('business_city')
                            ->label('Area / city')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Kanchan, Rupganj'),
                        TextInput::make('business_region')
                            ->label('District')
                            ->maxLength(100)
                            ->placeholder('Narayanganj')
                            ->helperText('The footer shows "Area / city, District".'),
                        TextInput::make('business_latitude')
                            ->label('Map latitude (optional)')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->placeholder('23.7104'),
                        TextInput::make('business_longitude')
                            ->label('Map longitude (optional)')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->placeholder('90.4074')
                            ->helperText('For search engines only. In Google Maps, right-click your office to copy both numbers.'),
                    ])
                    ->columns(2),
                Section::make('Social links')
                    ->description('Leave a field empty to hide that icon in the footer.')
                    ->schema([
                        TextInput::make('social_facebook')->label('Facebook')->url()->maxLength(255),
                        TextInput::make('social_twitter')->label('X (Twitter)')->url()->maxLength(255),
                        TextInput::make('social_youtube')->label('YouTube')->url()->maxLength(255),
                        TextInput::make('social_linkedin')->label('LinkedIn')->url()->maxLength(255),
                        TextInput::make('reviews_url')
                            ->label('Reviews page')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://g.page/r/…/review')
                            ->helperText('Where "Read more" under customer testimonials goes, e.g. your Google reviews or Facebook reviews. Empty hides "Read more".'),
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

        foreach (BusinessInfo::KEYS as $key) {
            $value = trim((string) ($state[$key] ?? ''));

            if ($key === 'client_area_url') {
                $value = rtrim($value, '/') . '/';
            }

            Setting::set($key, $value);
        }

        Notification::make()->success()->title('Saved')->body('The website shows the new details now.')->send();
    }
}
