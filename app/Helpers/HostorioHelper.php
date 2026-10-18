<?php

namespace App\Helpers {
    use App\Services\SettingsService;

    class HostorioHelper
    {
        protected static $settingsService;

        public static function get($key, $default = null)
        {
            if (!self::$settingsService) {
                self::$settingsService = app(SettingsService::class);
            }

            $value = self::$settingsService->get($key);
            return $value !== null ? $value : $default;
        }
    }
}

namespace {
    if (!function_exists('hostorio')) {
        function hostorio($key, $default = null)
        {
            return \App\Helpers\HostorioHelper::get($key, $default);
        }
    }

    if (!function_exists('hostorio_products')) {
        /** Visible products of a page, in admin order. */
        function hostorio_products(string $page): \Illuminate\Support\Collection
        {
            return app(\App\Services\ProductCatalog::class)->forPage($page);
        }
    }
}
