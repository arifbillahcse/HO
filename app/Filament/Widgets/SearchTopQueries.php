<?php

namespace App\Filament\Widgets;

use App\Services\GoogleReports;

class SearchTopQueries extends GoogleTableWidget
{
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return app(GoogleReports::class)->hasSearchConsole();
    }

    protected function heading(): string
    {
        return 'Top searches';
    }

    protected function columns(): array
    {
        return ['Search', 'Clicks', 'Impressions', 'Position'];
    }

    protected function rows(int $days): array
    {
        return array_map(fn (array $r) => [
            $r['key'], number_format($r['clicks']), number_format($r['impressions']), number_format($r['position'], 1),
        ], static::reports()->searchTop('query', $days, 10));
    }
}
