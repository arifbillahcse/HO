<?php

namespace App\Filament\Pages;

use App\Services\GoogleReports;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('period')
                ->label('Period')
                ->options(GoogleReports::PERIODS)
                ->default(28)
                ->selectablePlaceholder(false)
                ->visible(fn (): bool => app(GoogleReports::class)->isConfigured()),
        ]);
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
