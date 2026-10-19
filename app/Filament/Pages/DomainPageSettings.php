<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\DomainPricing;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class DomainPageSettings extends Page
{
    protected static ?string $title = 'Domain page';
    protected static ?string $navigationLabel = 'Domain page';
    protected static ?string $slug = 'domain-page';
    protected static string | UnitEnum | null $navigationGroup = 'Domains';
    protected static ?int $navigationSort = 2;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getSubheading(): ?string
    {
        return 'Questions and answers on the Domain Registration page. Its title and description for Google are under SEO → Page SEO.';
    }

    public function mount(): void
    {
        $faq = SettingsService::get('domain_faq');

        $this->form->fill(['faq' => is_array($faq) && $faq !== [] ? $faq : DomainPricing::DEFAULT_FAQ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('FAQ')
                    ->description('English only, like the plan details. Put a live price in an answer with {register:.com}, {renew:.com} or {transfer:.com} (any extension). Google also gets these questions as FAQ data.')
                    ->schema([
                        Repeater::make('faq')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('question')->required()->maxLength(255),
                                Textarea::make('answer')->required()->rows(3)->maxLength(2000),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                            ->collapsible()
                            ->reorderableWithDragAndDrop()
                            ->addActionLabel('Add question'),
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
                            Action::make('save')->label('Save')->submit('save')->keyBindings(['mod+s']),
                            Action::make('view')->label('View page')->color('gray')
                                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                                ->url(hostorio_url('domain-registration'))->openUrlInNewTab(),
                        ])->key('form-actions'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $faq = array_values(array_map(fn (array $item) => [
            'question' => trim((string) $item['question']),
            'answer' => trim((string) $item['answer']),
        ], $this->form->getState()['faq'] ?? []));

        Setting::set('domain_faq', $faq);

        Notification::make()->success()->title('Saved')->send();
    }
}
