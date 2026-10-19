<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Models\Setting;
use App\Services\BusinessInfo;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $modelLabel = 'setting';
    protected static ?string $recordTitleAttribute = 'key';
    protected static ?int $navigationSort = 100;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit'),
                Textarea::make('value')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('key', 'not like', 'whmcs%')->where('key', 'not like', 'seo%')->where('key', 'not like', 'biz%')->whereNotIn('key', BusinessInfo::KEYS))
            ->columns([
                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->limit(50)
                    ->wrap(),
            ])
            ->defaultSort('key')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
