<?php

namespace App\Filament\Resources\NotFoundLogs;

use App\Filament\Resources\NotFoundLogs\Pages\ManageNotFoundLogs;
use App\Filament\Resources\Redirects\RedirectResource;
use App\Models\NotFoundLog;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class NotFoundLogResource extends Resource
{
    protected static ?string $model = NotFoundLog::class;
    protected static string | UnitEnum | null $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = '404 Monitor';
    protected static ?string $modelLabel = '404 address';
    protected static ?string $pluralModelLabel = '404 Monitor';
    protected static ?int $navigationSort = 5;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('path')->label('Address')->searchable()->copyable()->weight('medium')->wrap(),
                TextColumn::make('hits')->label('Hits')->numeric()->sortable(),
                TextColumn::make('last_hit_at')->label('Last seen')->since()->sortable(),
                TextColumn::make('referrer')->label('Came from')->limit(50)->placeholder('—')->toggleable(),
                TextColumn::make('user_agent')->label('Browser / bot')->limit(50)->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_hit_at', 'desc')
            ->paginated([25, 50, 100])
            ->recordActions([
                Action::make('redirect')
                    ->label('Redirect')
                    ->icon(Heroicon::OutlinedArrowsRightLeft)
                    ->modalHeading(fn (NotFoundLog $record): string => "Redirect {$record->path}")
                    ->schema(RedirectResource::targetFields())
                    ->action(function (NotFoundLog $record, array $data): void {
                        static::redirect(collect([$record]), $data);
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('redirect')
                        ->label('Redirect selected')
                        ->icon(Heroicon::OutlinedArrowsRightLeft)
                        ->schema(RedirectResource::targetFields())
                        ->action(fn (Collection $records, array $data) => static::redirect($records, $data))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No 404 errors recorded')
            ->emptyStateDescription('Addresses that do not exist on the site show up here when someone visits them.');
    }

    /** Create (or update) a redirect for each address and remove it from the log. */
    public static function redirect(iterable $records, array $data): void
    {
        $count = 0;

        foreach ($records as $record) {
            $source = Redirect::normalize($record->path);

            if (in_array($source, ['/', '/*'], true) || Redirect::isIgnored($source)) {
                continue;
            }

            Redirect::updateOrCreate(['source' => $source], [
                'target' => (int) $data['status_code'] === 410 ? null : $data['target'],
                'status_code' => (int) $data['status_code'],
                'is_active' => true,
            ]);
            $record->delete();
            $count++;
        }

        Notification::make()->success()->title($count === 1 ? 'Redirect created' : "{$count} redirects created")->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageNotFoundLogs::route('/'),
        ];
    }
}
