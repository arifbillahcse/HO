<?php

namespace App\Filament\Resources\Settings;

use App\Models\Setting;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\CreateSetting;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?int $navigationSort = 100;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->limit(50)
                    ->wrap(),
            ])
            ->filters([])
            ->actions([
                // Edit actions
            ])
            ->bulkActions([
                // Bulk actions
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}
