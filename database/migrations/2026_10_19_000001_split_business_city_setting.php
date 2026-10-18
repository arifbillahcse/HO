<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * "Kanchan, Rupganj, Narayanganj" becomes city "Kanchan, Rupganj" and region
 * "Narayanganj", so the address can be edited as separate fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        $city = Setting::where('key', 'business_city')->first();

        if (! $city || ! is_string($city->value) || Setting::where('key', 'business_region')->exists()) {
            return;
        }

        $parts = array_map('trim', explode(',', $city->value));
        $region = count($parts) > 1 ? array_pop($parts) : '';

        $city->update(['value' => implode(', ', $parts)]);
        Setting::set('business_region', $region);
    }

    public function down(): void
    {
        $city = Setting::where('key', 'business_city')->first();
        $region = Setting::get('business_region');

        if ($city && filled($region)) {
            $city->update(['value' => "{$city->value}, {$region}"]);
        }

        Setting::where('key', 'business_region')->delete();
    }
};
