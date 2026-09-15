<?php

namespace App\Helpers;

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

if (!function_exists('hostorio')) {
    function hostorio($key, $default = null)
    {
        return \App\Helpers\HostorioHelper::get($key, $default);
    }
}
