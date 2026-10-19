<?php

namespace App\Filament\Widgets;

use App\Services\GoogleReports;

class SearchTopPages extends GoogleTableWidget
{
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return app(GoogleReports::class)->hasSearchConsole();
    }

    protected function heading(): string
    {
        return 'Top pages in Google';
    }

    protected function columns(): array
    {
        return ['Page', 'Clicks', 'Impressions', 'Position'];
    }

    protected function rows(int $days): array
    {
        return array_map(fn (array $r) => [
            static::path($r['key']), number_format($r['clicks']), number_format($r['impressions']), number_format($r['position'], 1),
        ], array_slice(static::reports()->searchTop('page', $days, 500), 0, 10));
    }
}
