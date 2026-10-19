<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\GoogleConnection;
use App\Services\GoogleReports;
use Filament\Widgets\Widget;

class GoogleSetupWidget extends Widget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.google-setup';
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return ! app(GoogleReports::class)->isConfigured();
    }

    protected function getViewData(): array
    {
        return ['url' => GoogleConnection::getUrl()];
    }
}
