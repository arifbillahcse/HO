<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;
    protected static string | UnitEnum | null $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'Redirects';
    protected static ?int $navigationSort = 3;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;
    protected static ?string $recordTitleAttribute = 'source';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            static::sourceField(),
            ...static::targetFields(),
            Toggle::make('is_active')->label('Active')->default(true),
        ])->columns(1);
    }

    public static function sourceField(): TextInput
    {
        return TextInput::make('source')
            ->label('Old address')
            ->required()
            ->maxLength(255)
            ->placeholder('/old-page/')
            ->helperText('A path or a full URL. End with * to match every address that starts with it, e.g. /blog/*. Upper/lower case and a trailing / do not matter.')
            ->dehydrateStateUsing(fn (?string $state): string => Redirect::normalize((string) $state))
            ->rule(fn (?Redirect $record): Closure => function (string $attribute, $value, Closure $fail) use ($record): void {
                $source = Redirect::normalize((string) $value);

                if (in_array($source, ['/', '/*'], true)) {
                    $fail('The homepage or the whole site cannot be redirected.');
                } elseif (Redirect::isIgnored(rtrim($source, '*'))) {
                    $fail('Admin, storage, sitemap and robots.txt addresses cannot be redirected.');
                } elseif (Redirect::query()->where('source', $source)->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))->exists()) {
                    $fail('A redirect for this address already exists.');
                }
            });
    }

    /** @return array<int, \Filament\Schemas\Components\Component> */
    public static function targetFields(): array
    {
        return [
            Select::make('status_code')
                ->label('Type')
                ->options(Redirect::TYPES)
                ->default(301)
                ->required()
                ->live()
                ->helperText('Use 301 for pages that moved for good; search engines then pass the ranking on to the new address.'),
            TextInput::make('target')
                ->label('New address')
                ->maxLength(500)
                ->placeholder('/web-hosting/')
                ->required(fn (Get $get): bool => (int) $get('status_code') !== 410)
                ->hidden(fn (Get $get): bool => (int) $get('status_code') === 410)
                ->helperText('A path on this site (/web-hosting/) or a full URL. With a * old address, a * here is replaced by the rest of the address.')
                ->rule(fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get): void {
                    $value = trim((string) $value);
                    $source = trim((string) $get('source')); // empty in the 404 Monitor, where the old address is the log entry

                    if (! str_starts_with($value, '/') && ! preg_match('#^https?://#i', $value)) {
                        $fail('Start with / for a page on this site, or with https:// for another site.');
                    } elseif ($source !== '' && str_starts_with($value, '/') && ! str_contains($value, '*')
                        && Redirect::normalize($value) === Redirect::normalize($source)) {
                        $fail('The new address is the same as the old one.');
                    }
                }),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source')->label('Old address')->searchable()->copyable()->weight('medium'),
                TextColumn::make('target')->label('New address')->searchable()->wrap()->placeholder('—'),
                TextColumn::make('status_code')->label('Type')->badge()
                    ->color(fn (int $state): string => $state === 301 ? 'success' : ($state === 410 ? 'danger' : 'warning')),
                TextColumn::make('hits')->label('Hits')->numeric()->sortable(),
                TextColumn::make('last_hit_at')->label('Last used')->since()->sortable()->placeholder('Never'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->recordActions([
                Action::make('test')
                    ->label('Test')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Redirect $record): string => url(str_replace('*', '', $record->source)))
                    ->openUrlInNewTab()
                    ->visible(fn (Redirect $record): bool => ! str_ends_with($record->source, '*')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('No redirects yet')
            ->emptyStateDescription('Add one here, or create one from the 404 Monitor when an old address shows up there.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRedirects::route('/'),
        ];
    }
}
