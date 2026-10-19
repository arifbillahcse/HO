<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesGooglePeriod;
use App\Services\GoogleReports;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Throwable;

class SearchConsoleOverview extends StatsOverviewWidget
{
    use UsesGooglePeriod;

    /** Google data changes a few times a day; no need to poll. */
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return app(GoogleReports::class)->hasSearchConsole();
    }

    protected function getHeading(): ?string
    {
        return 'Google Search · ' . $this->periodLabel();
    }

    protected function getDescription(): ?string
    {
        return 'From Search Console. Google adds the newest days with a delay of about two days.';
    }

    protected function getStats(): array
    {
        try {
            $data = static::reports()->searchTotals($this->days());
        } catch (Throwable $e) {
            return [Stat::make('Search Console', 'Not available')->description($e->getMessage())->color('danger')];
        }

        [$now, $before] = [$data['current'], $data['previous']];
        $clicks = array_column($data['daily'], 'clicks');
        $impressions = array_column($data['daily'], 'impressions');

        $stat = function (string $label, string $value, array $change, ?array $chart = null): Stat {
            [$text, $color, $icon] = $change;

            return Stat::make($label, $value)->description($text)->descriptionIcon($icon)->color($color)->chart($chart);
        };

        return [
            $stat('Clicks', number_format($now['clicks']), $this->change($now['clicks'], $before['clicks']), $clicks),
            $stat('Impressions', static::compact($now['impressions']), $this->change($now['impressions'], $before['impressions']), $impressions),
            $stat('Click-through rate', number_format($now['ctr'], 1) . '%', $this->change($now['ctr'], $before['ctr'], absolute: true)),
            $stat('Average position', $now['position'] > 0 ? number_format($now['position'], 1) : '—', $this->change($now['position'], $before['position'], lowerIsBetter: true, absolute: true)),
        ];
    }
}
