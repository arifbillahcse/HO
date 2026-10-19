<?php

namespace App\Filament\Widgets;

use App\Services\GoogleReports;

class AnalyticsTopPages extends GoogleTableWidget
{
    protected static ?int $sort = 7;

    public static function canView(): bool
    {
        return app(GoogleReports::class)->hasAnalytics();
    }

    protected function heading(): string
    {
        return 'Most visited pages';
    }

    protected function columns(): array
    {
        return ['Page', 'Views', 'Users'];
    }

    protected function rows(int $days): array
    {
        return array_map(fn (array $r) => [
            $r['key'], number_format($r['value']), number_format($r['secondary']),
        ], static::reports()->analyticsTop('pagePath', 'screenPageViews', 'activeUsers', $days));
    }
}
