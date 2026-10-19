<?php

namespace App\Filament\Resources\DomainExtensions;

use App\Filament\Resources\DomainExtensions\Pages\ManageDomainExtensions;
use App\Models\DomainExtension;
use App\Services\DomainPricing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DomainExtensionResource extends Resource
{
    protected static ?string $model = DomainExtension::class;
    protected static string | UnitEnum | null $navigationGroup = 'Domains';
    protected static ?string $navigationLabel = 'Domain prices';
    protected static ?string $modelLabel = 'extension';
    protected static ?string $pluralModelLabel = 'Domain prices';
    protected static ?int $navigationSort = 1;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    public static function canCreate(): bool
    {
        return false; // the list comes from WHMCS
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tld')->label('Extension')->weight('bold')->searchable(),
                TextColumn::make('register')->label('Register')
                    ->state(fn (DomainExtension $record): string => static::price($record, 'register')),
                TextColumn::make('renew')->label('Renew')
                    ->state(fn (DomainExtension $record): string => static::price($record, 'renew')),
                TextColumn::make('transfer')->label('Transfer')
                    ->state(fn (DomainExtension $record): string => static::price($record, 'transfer')),
                SelectColumn::make('badge')
                    ->label('Badge')
                    ->options(DomainExtension::BADGES)
                    ->placeholder('As in WHMCS')
                    ->selectablePlaceholder(true),
                ToggleColumn::make('is_visible')->label('Show on website'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated([50, 100, 'all'])
            ->defaultPaginationPageOption(50)
            ->emptyStateHeading('No extensions yet')
            ->emptyStateDescription('Extensions appear here once the WHMCS connection works. Set it up under WHMCS Connection, then click "Refresh from WHMCS".');
    }

    /** Price from WHMCS for the list, e.g. "৳1,499". */
    public static function price(DomainExtension $record, string $type): string
    {
        return app(DomainPricing::class)->price($record->tld, $type)['bdt'] ?? '—';
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDomainExtensions::route('/'),
        ];
    }
}
