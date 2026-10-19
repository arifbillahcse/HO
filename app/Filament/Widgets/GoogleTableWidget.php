<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesGooglePeriod;
use Filament\Widgets\Widget;
use Throwable;

/**
 * A small "top 10" table from Search Console or Analytics.
 */
abstract class GoogleTableWidget extends Widget
{
    use UsesGooglePeriod;

    protected string $view = 'filament.widgets.google-table';

    abstract protected function heading(): string;

    /** @return list<string> column titles; the first is left-aligned, the rest right-aligned */
    abstract protected function columns(): array;

    /** @return list<list<string>> formatted cells */
    abstract protected function rows(int $days): array;

    protected function emptyText(): string
    {
        return 'No data for this period yet.';
    }

    protected function getViewData(): array
    {
        try {
            $rows = $this->rows($this->days());
            $error = null;
        } catch (Throwable $e) {
            $rows = [];
            $error = $e->getMessage();
        }

        return [
            'heading' => $this->heading(),
            'description' => ucfirst($this->periodLabel()),
            'columns' => $this->columns(),
            'rows' => $rows,
            'error' => $error,
            'empty' => $this->emptyText(),
        ];
    }

    protected static function path(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $query = parse_url($url, PHP_URL_QUERY);

        return $path . ($query ? "?{$query}" : '');
    }
}
