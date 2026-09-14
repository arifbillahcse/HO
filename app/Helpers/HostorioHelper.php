<?php

namespace App\Helpers;

use App\Services\SettingsService;

if (!function_exists('hostorio')) {
    function hostorio(string $key, $default = null)
    {
        return SettingsService::get($key, $default);
    }
}

if (!function_exists('hostorio_url')) {
    function hostorio_url(string $name, $parameters = [])
    {
        return route($name, $parameters);
    }
}
