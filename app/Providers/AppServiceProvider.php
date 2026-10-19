<?php

namespace App\Providers;

use App\Services\WhmcsService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/HostorioHelper.php');

        $this->app->singleton(WhmcsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Behind a proxy that talks plain http to the server (e.g. Cloudflare "Flexible" SSL),
        // keep every generated link, canonical and asset on https so nothing is blocked as mixed content.
        if ($this->app->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
