<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesGooglePeriod;
use App\Services\GoogleReports;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Throwable;

class AnalyticsOverview extends StatsOverviewWidget
{
    use UsesGooglePeriod;

    /** Google data changes a few times a day; no need to poll. */
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return app(GoogleReports::class)->hasAnalytics();
    }

    protected function getHeading(): ?string
    {
        return 'Visitors · ' . $this->periodLabel();
    }

    protected function getDescription(): ?string
    {
        return 'From Google Analytics.';
    }

    protected function getStats(): array
    {
        try {
            $data = static::reports()->analyticsTotals($this->days());
        } catch (Throwable $e) {
            return [Stat::make('Google Analytics', 'Not available')->description($e->getMessage())->color('danger')];
        }

        $stats = [];
        foreach (['users' => 'Users', 'sessions' => 'Sessions', 'views' => 'Page views'] as $key => $label) {
            [$text, $color, $icon] = $this->change($data['current'][$key], $data['previous'][$key]);
            $stats[] = Stat::make($label, static::compact($data['current'][$key]))
                ->description($text)->descriptionIcon($icon)->color($color)
                ->chart(array_column($data['daily'], $key));
        }

        return $stats;
    }
}
