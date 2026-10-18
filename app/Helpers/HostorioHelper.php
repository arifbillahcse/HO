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

    if (!function_exists('hostorio_business')) {
        /** Contact details and address from Admin → Contact & Address. */
        function hostorio_business(): \App\Services\BusinessInfo
        {
            return app(\App\Services\BusinessInfo::class);
        }
    }

    if (!function_exists('hostorio_seo')) {
        /** Titles, meta tags and tracking code from Admin → SEO. */
        function hostorio_seo(): \App\Services\Seo
        {
            return app(\App\Services\Seo::class);
        }
    }

    if (!function_exists('hostorio_url')) {
        /** Address of a website page with the trailing slash, e.g. hostorio_url('web-hosting'). */
        function hostorio_url(string $slug = ''): string
        {
            return \App\Services\Seo::url($slug);
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
