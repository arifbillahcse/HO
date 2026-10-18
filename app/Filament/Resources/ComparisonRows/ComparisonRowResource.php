<?php

namespace App\Filament\Resources\ComparisonRows;

use App\Filament\Resources\ComparisonRows\Pages\ManageComparisonRows;
use App\Models\ComparisonRow;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ComparisonRowResource extends Resource
{
    protected static ?string $model = ComparisonRow::class;
    protected static string | UnitEnum | null $navigationGroup = 'Products & Services';
    protected static ?string $navigationLabel = 'Comparison rows';
    protected static ?string $modelLabel = 'comparison row';
    protected static ?int $navigationSort = 2;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTableCells;
    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('page_key')
                ->label('Page')
                ->options(Product::PAGES)
                ->required()
                ->native(false),
            TextInput::make('label')
                ->label('Row label')
                ->placeholder('e.g. Storage Space')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        $pageOrder = 'CASE page_key ' . collect(array_keys(Product::PAGES))
            ->map(fn (string $page, int $i) => "WHEN '{$page}' THEN {$i}")->implode(' ') . ' ELSE 99 END';

        return $table
            ->columns([
                TextColumn::make('label')->label('Row')->searchable(),
            ])
            ->groups([
                Group::make('page_key')
                    ->label('Page')
                    ->getTitleFromRecordUsing(fn (ComparisonRow $record): string => Product::PAGES[$record->page_key] ?? $record->page_key)
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageComparisonRows::route('/'),
        ];
    }
}
