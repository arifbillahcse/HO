<?php

namespace App\Filament\Widgets;

use App\Services\GoogleReports;

class AnalyticsChannels extends GoogleTableWidget
{
    protected static ?int $sort = 8;

    public static function canView(): bool
    {
        return app(GoogleReports::class)->hasAnalytics();
    }

    protected function heading(): string
    {
        return 'Where visitors come from';
    }

    protected function columns(): array
    {
        return ['Source', 'Sessions', 'Share'];
    }

    protected function rows(int $days): array
    {
        $rows = static::reports()->analyticsTop('sessionDefaultChannelGroup', 'sessions', null, $days);
        $total = array_sum(array_column($rows, 'value')) ?: 1;

        return array_map(fn (array $r) => [
            $r['key'], number_format($r['value']), number_format($r['value'] / $total * 100) . '%',
        ], $rows);
    }
}
