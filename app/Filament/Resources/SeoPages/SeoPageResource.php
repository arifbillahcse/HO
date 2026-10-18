<?php

namespace App\Filament\Resources\SeoPages;

use App\Filament\Resources\SeoPages\Pages\EditSeoPage;
use App\Filament\Resources\SeoPages\Pages\ListSeoPages;
use App\Models\SeoPage;
use App\Services\Seo;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SeoPageResource extends Resource
{
    protected static ?string $model = SeoPage::class;
    protected static string | UnitEnum | null $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'Page SEO';
    protected static ?string $modelLabel = 'page';
    protected static ?string $pluralModelLabel = 'Page SEO';
    protected static ?int $navigationSort = 1;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;
    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('SEO')
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Google')
                        ->icon(Heroicon::OutlinedMagnifyingGlass)
                        ->schema([
                            View::make('filament.seo.google-preview')
                                ->viewData(fn (Get $get, ?SeoPage $record): array => static::preview($get, $record)),
                            TextInput::make('seo_title')
                                ->label('SEO title')
                                ->maxLength(255)
                                ->live(debounce: 400)
                                ->placeholder(fn (): string => app(Seo::class)->pattern())
                                ->helperText(fn (Get $get): string => static::titleHelp($get)),
                            Textarea::make('meta_description')
                                ->label('Meta description')
                                ->rows(3)
                                ->maxLength(500)
                                ->live(debounce: 400)
                                ->helperText(fn (Get $get): string => static::descriptionHelp((string) $get('meta_description'))),
                            TextInput::make('focus_keyword')
                                ->label('Focus keyword')
                                ->maxLength(100)
                                ->placeholder('e.g. web hosting bangladesh')
                                ->helperText('The main search phrase you want this page to rank for.'),
                        ]),

                    Tab::make('Social')
                        ->icon(Heroicon::OutlinedShare)
                        ->schema([
                            Section::make()
                                ->description('How the page looks when shared on Facebook, X, LinkedIn or WhatsApp. Leave empty to use the SEO title and meta description.')
                                ->schema([
                                    TextInput::make('og_title')
                                        ->label('Social title')
                                        ->maxLength(255)
                                        ->placeholder('Same as the SEO title'),
                                    Textarea::make('og_description')
                                        ->label('Social description')
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->placeholder('Same as the meta description'),
                                    FileUpload::make('og_image')
                                        ->label('Social image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('seo')
                                        ->visibility('public')
                                        ->maxSize(5120)
                                        ->helperText('1200 × 630 pixels works best. Leave empty to use the default share image from SEO Settings.'),
                                ]),
                        ]),

                    Tab::make('Advanced')
                        ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                        ->schema([
                            TextInput::make('name')
                                ->label('Page name')
                                ->required()
                                ->maxLength(100)
                                ->live(debounce: 400)
                                ->helperText('Fills %title% in the title pattern, e.g. "Web Hosting" → "Web Hosting - Fast Web Hosting ৳999/yr | Free SSL & Domain | Hostorio".'),
                            Toggle::make('robots_index')
                                ->label('Show this page in search results (index)')
                                ->live()
                                ->helperText('Switch off to add "noindex": Google drops the page and it leaves the sitemap.'),
                            Toggle::make('robots_follow')
                                ->label('Let search engines follow links on this page (follow)'),
                            TextInput::make('canonical_url')
                                ->label('Canonical URL (optional)')
                                ->url()
                                ->maxLength(255)
                                ->live(debounce: 400)
                                ->placeholder(fn (?SeoPage $record): string => Seo::url($record?->slug() ?? ''))
                                ->helperText('Leave empty. Only set this if another address should rank instead of this page.'),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Page')
                    ->weight('medium')
                    ->description(fn (SeoPage $record): string => '/' . ltrim($record->slug() . '/', '/')),
                TextColumn::make('seo_title')
                    ->label('Title in Google')
                    ->state(fn (SeoPage $record): string => app(Seo::class)->title($record))
                    ->wrap(),
                TextColumn::make('meta_description')
                    ->label('Description')
                    ->state(fn (SeoPage $record): string => filled($record->meta_description) ? mb_strlen($record->meta_description) . ' characters' : 'Missing')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Missing' ? 'warning' : 'success'),
                TextColumn::make('focus_keyword')
                    ->label('Focus keyword')
                    ->placeholder('—'),
                IconColumn::make('robots_index')
                    ->label('Indexed')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->paginated(false)
            ->recordActions([
                EditAction::make(),
                Action::make('view')
                    ->label('View page')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (SeoPage $record): string => Seo::url($record->slug()))
                    ->openUrlInNewTab(),
            ]);
    }

    /** Data for the live Google preview. */
    public static function preview(Get $get, ?SeoPage $record): array
    {
        $seo = app(Seo::class);
        $name = (string) ($get('name') ?: $record?->name);

        return [
            'title' => $seo->resolveTitle($get('seo_title'), $name),
            'description' => trim((string) $get('meta_description')),
            'url' => $get('canonical_url') ?: Seo::url($record?->slug() ?? ''),
            'brand' => hostorio_business()->name(),
            'favicon' => $seo->faviconUrl(),
            'indexed' => (bool) $get('robots_index') && ! $seo->isHidden(),
        ];
    }

    private static function titleHelp(Get $get): string
    {
        $length = mb_strlen(app(Seo::class)->resolveTitle($get('seo_title'), (string) $get('name')));
        $advice = $length > 60 ? 'Google usually cuts titles after about 60 characters.' : 'Good length.';

        return "{$length} characters. {$advice} Leave empty to use the default pattern. You can use %title% (page name), %sep% (separator) and %sitename% (site name).";
    }

    private static function descriptionHelp(string $description): string
    {
        $length = mb_strlen(trim($description));

        return match (true) {
            $length === 0 => 'Empty: Google writes its own snippet from the page text. 120–160 characters works best.',
            $length < 120 => "{$length} characters. A little short: 120–160 works best.",
            $length <= 160 => "{$length} characters. Good length.",
            default => "{$length} characters. Google usually cuts descriptions after about 160 characters.",
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoPages::route('/'),
            'edit' => EditSeoPage::route('/{record}/edit'),
        ];
    }
}
