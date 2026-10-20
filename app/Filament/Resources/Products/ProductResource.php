<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\ComparisonRow;
use App\Models\Product;
use App\Services\ProductCatalog;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static string | UnitEnum | null $navigationGroup = 'Products & Services';
    protected static ?string $navigationLabel = 'Products';
    protected static ?int $navigationSort = 1;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCube;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Product')
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Card')
                        ->icon(Heroicon::OutlinedRectangleStack)
                        ->columns(2)
                        ->schema([
                            Select::make('page_key')
                                ->label('Page')
                                ->options(Product::PAGES)
                                ->required()
                                ->native(false)
                                ->live(),
                            TextInput::make('name')
                                ->label('Plan name')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label('Short description')
                                ->rows(2)
                                ->maxLength(500)
                                ->columnSpanFull(),
                            TextInput::make('button_text')
                                ->label('Button text')
                                ->default('Order Now')
                                ->required()
                                ->maxLength(60),
                            Toggle::make('is_visible')
                                ->label('Show on website')
                                ->default(true)
                                ->inline(false),
                            Toggle::make('is_popular')
                                ->label('Highlight this plan')
                                ->helperText('Adds an orange border and a badge on top of the card.')
                                ->live()
                                ->inline(false),
                            TextInput::make('badge_text')
                                ->label('Badge text')
                                ->default('Most Popular')
                                ->maxLength(60)
                                ->visible(fn (Get $get): bool => (bool) $get('is_popular'))
                                ->required(fn (Get $get): bool => (bool) $get('is_popular')),
                            Toggle::make('show_original_price')
                                ->label('Show crossed-out price and "Up to 30%!" badge')
                                ->helperText('The crossed-out price is 12 months at the WHMCS monthly price, so it only shows for plans that have a monthly price.')
                                ->default(true)
                                ->inline(false)
                                ->columnSpanFull(),
                            FileUpload::make('icon')
                                ->label('Icon')
                                ->image()
                                ->disk('public')
                                ->directory('products')
                                ->visibility('public')
                                ->maxSize(1024)
                                ->helperText('Leave empty to use the default server icon.'),
                            TextInput::make('icon_alt')
                                ->label('Icon description (alt text)')
                                ->helperText('Describes the plan for search engines and screen readers.')
                                ->maxLength(255),
                        ]),

                    Tab::make('Specifications')
                        ->icon(Heroicon::OutlinedListBullet)
                        ->schema([
                            Repeater::make('features')
                                ->hiddenLabel()
                                ->table([
                                    TableColumn::make('Bold value')->width('25%'),
                                    TableColumn::make('Text')->markAsRequired(),
                                    TableColumn::make('NEW tag')->width('90px'),
                                ])
                                ->schema([
                                    TextInput::make('value')->placeholder('e.g. 10GB')->maxLength(100),
                                    TextInput::make('text')->placeholder('e.g. NVMe Storage')->required()->maxLength(255),
                                    Toggle::make('is_new'),
                                ])
                                ->reorderable()
                                ->defaultItems(0)
                                ->addActionLabel('Add specification'),
                        ]),

                    Tab::make('Comparison')
                        ->icon(Heroicon::OutlinedTableCells)
                        ->visible(fn (Get $get): bool => filled($get('page_key')) && ComparisonRow::query()->where('page_key', $get('page_key'))->exists())
                        ->schema(fn (Get $get): array => static::comparisonFields($get('page_key'))),

                    Tab::make('Price & WHMCS')
                        ->icon(Heroicon::OutlinedCurrencyDollar)
                        ->columns(2)
                        ->schema([
                            TextInput::make('whmcs_pid')
                                ->label('WHMCS product ID')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->helperText('The live price and the order link come from this WHMCS product.'),
                            TextInput::make('yearly_price')
                                ->label('Fallback yearly price')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('৳')
                                ->helperText('In BDT. Shown when WHMCS has no price for this product.'),
                            TextInput::make('monthly_price')
                                ->label('Fallback monthly price')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('৳')
                                ->helperText('In BDT. Leave empty if the plan is sold yearly only. When WHMCS is connected, its monthly price is used instead.'),
                            TextInput::make('order_url')
                                ->label('Order link (optional)')
                                ->url()
                                ->maxLength(255)
                                ->columnSpanFull()
                                ->helperText('Leave empty to link to the WHMCS cart for the product ID above.'),
                        ]),
                ]),
        ]);
    }

    /** One row per comparison-table line of the chosen page: Yes / No / Text. */
    public static function comparisonFields(?string $page): array
    {
        if (blank($page)) {
            return [];
        }

        return ComparisonRow::query()->onPage($page)->get()->map(fn (ComparisonRow $row) => Grid::make(3)
            ->key("comparison-row-{$row->id}")
            ->schema([
                ToggleButtons::make("comparison.{$row->id}.type")
                    ->label($row->label)
                    ->options(['yes' => 'Yes', 'no' => 'No', 'text' => 'Text'])
                    ->icons(['yes' => Heroicon::Check, 'no' => Heroicon::XMark, 'text' => Heroicon::OutlinedPencil])
                    ->colors(['yes' => 'success', 'no' => 'danger', 'text' => 'gray'])
                    ->default('text')
                    ->inline()
                    ->live(),
                TextInput::make("comparison.{$row->id}.text")
                    ->label('Value')
                    ->maxLength(255)
                    ->columnSpan(2)
                    ->visible(fn (Get $get): bool => $get("comparison.{$row->id}.type") === 'text'),
            ]))->all();
    }

    /** Price as shown on the site (from WHMCS when connected). */
    private static function monthlyLabel(Product $record): string
    {
        if (! in_array($record->page_key, Product::MONTHLY_PAGES, true)) {
            return 'Yearly only';
        }

        $monthly = app(ProductCatalog::class)->monthlyPrice($record);

        return $monthly === null ? 'Missing' : '৳' . number_format((float) $monthly) . '/mo';
    }

    public static function table(Table $table): Table
    {
        $pageOrder = 'CASE page_key ' . collect(array_keys(Product::PAGES))
            ->map(fn (string $page, int $i) => "WHEN '{$page}' THEN {$i}")->implode(' ') . ' ELSE 99 END';

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Plan')
                    ->weight('medium')
                    ->description(fn (Product $record): ?string => $record->description)
                    ->searchable(),
                TextColumn::make('yearly_price')
                    ->label('Yearly')
                    ->state(fn (Product $record): string => app(ProductCatalog::class)->price($record))
                    ->formatStateUsing(fn ($state): string => '৳' . number_format((float) $state) . '/yr')
                    ->placeholder('—'),
                TextColumn::make('monthly_price')
                    ->label('Monthly')
                    ->state(fn (Product $record): string => self::monthlyLabel($record))
                    ->color(fn (string $state): ?string => $state === 'Missing' ? 'danger' : ($state === 'Yearly only' ? 'gray' : null))
                    ->tooltip(fn (string $state): ?string => $state === 'Missing' ? 'No monthly price in WHMCS: in monthly view this plan shows its yearly price.' : null),
                TextColumn::make('whmcs_pid')
                    ->label('WHMCS ID')
                    ->placeholder('—'),
                IconColumn::make('is_popular')
                    ->label('Popular')
                    ->boolean(),
                ToggleColumn::make('is_visible')
                    ->label('Visible'),
            ])
            ->groups([
                Group::make('page_key')
                    ->label('Page')
                    ->getTitleFromRecordUsing(fn (Product $record): string => $record->pageLabel())
                    ->orderQueryUsing(fn (Builder $query) => $query->orderByRaw($pageOrder))
                    ->collapsible(),
            ])
            ->defaultGroup('page_key')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated([25, 50, 100, 'all'])
            ->defaultPaginationPageOption(50)
            ->filters([
                SelectFilter::make('page_key')->label('Page')->options(Product::PAGES),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->beforeReplicaSaved(function (Product $replica): void {
                        $replica->name .= ' (copy)';
                        $replica->is_visible = false;
                        $replica->sort_order = (int) Product::query()->where('page_key', $replica->page_key)->max('sort_order') + 1;
                    })
                    ->successRedirectUrl(fn (Product $replica): string => static::getUrl('edit', ['record' => $replica])),
                DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    /** Keep only comparison values that belong to rows of the product's page. */
    public static function cleanComparison(array $data): array
    {
        $rowIds = ComparisonRow::query()->where('page_key', $data['page_key'] ?? null)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $data['comparison'] = collect($data['comparison'] ?? [])
            ->filter(fn ($value, $rowId) => in_array((string) $rowId, $rowIds, true))
            ->map(fn ($value) => ($value['type'] ?? 'text') === 'text'
                ? ['type' => 'text', 'text' => $value['text'] ?? '']
                : ['type' => $value['type']])
            ->all();

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
