<?php

namespace App\Filament\Widgets\Concerns;

use App\Services\GoogleReports;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

trait UsesGooglePeriod
{
    use InteractsWithPageFilters;

    protected function days(): int
    {
        $days = (int) ($this->pageFilters['period'] ?? 28);

        return array_key_exists($days, GoogleReports::PERIODS) ? $days : 28;
    }

    protected function periodLabel(): string
    {
        return mb_strtolower(GoogleReports::PERIODS[$this->days()]);
    }

    protected static function reports(): GoogleReports
    {
        return app(GoogleReports::class);
    }

    /**
     * "+12% vs previous period", coloured; for average position a lower number is better.
     *
     * @return array{0: string, 1: string, 2: Heroicon|null}
     */
    protected function change(float $current, float $previous, bool $lowerIsBetter = false, bool $absolute = false): array
    {
        if ($previous == 0.0) {
            return [$current > 0 ? 'New this period' : 'No data yet', 'gray', null];
        }

        $delta = $absolute ? $current - $previous : ($current - $previous) / $previous * 100;
        $text = ($delta >= 0 ? '+' : '') . number_format($delta, $absolute ? 1 : 0) . ($absolute ? '' : '%') . ' vs previous ' . $this->days() . ' days';
        $better = $lowerIsBetter ? $delta < 0 : $delta > 0;

        return [$text, abs($delta) < 0.05 ? 'gray' : ($better ? 'success' : 'danger'), $delta >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown];
    }

    protected static function compact(float $number): string
    {
        return match (true) {
            $number >= 1_000_000 => number_format($number / 1_000_000, 1) . 'M',
            $number >= 10_000 => number_format($number / 1_000, 1) . 'K',
            default => number_format($number),
        };
    }
}
