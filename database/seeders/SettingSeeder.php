<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'business_name' => 'Hostorio',
            'business_phone' => '+8801779440297',
            'business_email' => 'mail@hostorio.com',
            'business_address' => 'H-161, OABDA SOROK MATHBARIA',
            'business_city' => 'Kanchan, Rupganj, Narayanganj',
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
            'translation_overrides_en' => json_encode([]),
            'translation_overrides_bn' => json_encode([]),
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
