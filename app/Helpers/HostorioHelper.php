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

    if (!function_exists('hostorio_plan_price')) {
        function hostorio_plan_price(string $page, int $slot, float | int $default): string
        {
            return app(\App\Services\WhmcsService::class)->planPrice($page, $slot, $default);
        }
    }

    if (!function_exists('hostorio_plan_url')) {
        function hostorio_plan_url(string $page, int $slot, string $default = '#'): string
        {
            return app(\App\Services\WhmcsService::class)->planUrl($page, $slot, $default);
        }
    }
}
