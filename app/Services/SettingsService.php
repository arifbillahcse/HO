<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'hostorio_settings';
    private const CACHE_TTL = 3600; // 1 hour

    public static function get(string $key, $default = null)
    {
        $settings = self::all();
        return data_get($settings, $key, $default);
    }

    public static function set(string $key, $value): void
    {
        Setting::set($key, $value);
        Cache::forget(self::CACHE_KEY);
    }

    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $settings = Setting::all()->mapWithKeys(fn ($s) => [$s->key => $s->value])->toArray();
            return array_merge(self::defaults(), $settings);
        });
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function defaults(): array
    {
        return [
            'business_name' => 'Hostorio',
            'business_phone' => '+8801779440297',
            'business_email' => 'mail@hostorio.com',
            'business_address' => 'H-161, OABDA SOROK MATHBARIA',
            'business_city' => 'Kanchan, Rupganj',
            'business_region' => 'Narayanganj',
            'business_latitude' => '23.7104',
            'business_longitude' => '90.4074',
            'client_area_url' => 'https://my.hostorio.com/',
            'social_facebook' => 'https://www.facebook.com/hostorio',
            'social_twitter' => 'https://x.com/hostorio',
            'social_youtube' => 'https://www.youtube.com/@arifbillahcse',
            'social_linkedin' => 'https://www.linkedin.com/company/hostorio/',
            'logo_url' => '',
            'footer_description' => 'Hostorio is a popular web hosting services provider. Hostorio provides Shared Hosting, Business Hosting, Reseller Hosting, VPS, and domain Registration-Transfer services.',
            'copyright' => '© ' . date('Y') . ' Hostorio. All Rights Reserved.',
            'exchange_rate' => 125,
            'default_currency' => 'BDT',
            'default_language' => 'en',
            'translation_overrides_en' => [],
            'translation_overrides_bn' => [],
            'seo_site_name' => Seo::DEFAULT_SITE_NAME,
            'seo_separator' => '-',
            'seo_title_pattern' => Seo::DEFAULT_PATTERN,
            'seo_ga4_id' => 'G-PJSW407GJS',
            'seo_footer_code' => '<script src="https://chat.hostorio.com/widget/widget.js" data-endpoint="https://chat.hostorio.com/api/chat" defer></script>',
        ];
    }
}
