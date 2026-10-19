<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesGooglePeriod;
use App\Services\GoogleReports;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Throwable;

class SearchPerformanceChart extends ChartWidget
{
    use UsesGooglePeriod;

    /** Google data changes a few times a day; no need to poll. */
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return app(GoogleReports::class)->hasSearchConsole();
    }

    public function getHeading(): string
    {
        return 'Clicks and impressions per day';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        try {
            $daily = static::reports()->searchTotals($this->days())['daily'];
        } catch (Throwable) {
            $daily = [];
        }

        return [
            'datasets' => [
                ['label' => 'Clicks', 'data' => array_column($daily, 'clicks'), 'borderColor' => '#d97706', 'backgroundColor' => 'rgba(217,119,6,.15)', 'yAxisID' => 'y', 'tension' => 0.3, 'pointRadius' => 0],
                ['label' => 'Impressions', 'data' => array_column($daily, 'impressions'), 'borderColor' => '#2563eb', 'backgroundColor' => 'rgba(37,99,235,.1)', 'yAxisID' => 'y1', 'tension' => 0.3, 'pointRadius' => 0],
            ],
            'labels' => array_map(fn (string $date) => Carbon::parse($date)->format('j M'), array_keys($daily)),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'scales' => [
                'y' => ['position' => 'left', 'beginAtZero' => true, 'title' => ['display' => true, 'text' => 'Clicks']],
                'y1' => ['position' => 'right', 'beginAtZero' => true, 'grid' => ['drawOnChartArea' => false], 'title' => ['display' => true, 'text' => 'Impressions']],
            ],
        ];
    }
}
