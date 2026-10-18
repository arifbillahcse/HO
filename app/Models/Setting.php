<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
    protected $casts = ['value' => 'json'];

    protected static function booted(): void
    {
        static::saved(fn () => SettingsService::flush());
        static::deleted(fn () => SettingsService::flush());
    }

    public static function get(string $key, $default = null)
    {
        return self::where('key', $key)->first()?->value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
