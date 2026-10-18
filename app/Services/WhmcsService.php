<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhmcsService
{
    public const DEFAULT_HERO_TLDS = ['.com', '.com.bd', '.xyz'];
    public const DEFAULT_CACHE_MINUTES = 360;

    private const FALLBACK_PRICES = [
        '.com' => '$12.99',
        '.com.bd' => '$9.50',
        '.xyz' => '$2.99',
    ];

    private const FRESH_KEY = 'whmcs_tld_pricing';
    private const STALE_KEY = 'whmcs_tld_pricing_last_good';
    private const BACKOFF_KEY = 'whmcs_tld_pricing_backoff';

    /**
     * @param  array{url?: ?string, identifier?: ?string, secret?: ?string, access_key?: ?string, currency_id?: int|string|null}|null  $credentials
     */
    public function __construct(private ?array $credentials = null)
    {
    }

    public static function withCredentials(array $credentials): self
    {
        return new self($credentials);
    }

    /* ───────────────────────── stored settings ───────────────────────── */

    public static function storeSecret(string $key, ?string $plain): void
    {
        Setting::set($key, filled($plain) ? Crypt::encryptString($plain) : null);
    }

    public static function hasStoredSecret(string $key = 'whmcs_api_secret'): bool
    {
        return self::readSecret($key) !== null;
    }

    private static function readSecret(string $key): ?string
    {
        $stored = SettingsService::get($key);

        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (Throwable) {
            return null; // e.g. APP_KEY changed since the secret was saved
        }
    }

    public function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        return [
            'url' => SettingsService::get('whmcs_url'),
            'identifier' => SettingsService::get('whmcs_api_identifier'),
            'secret' => self::readSecret('whmcs_api_secret'),
            'access_key' => self::readSecret('whmcs_api_access_key'),
            'currency_id' => SettingsService::get('whmcs_currency_id'),
        ];
    }

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return filled($c['url'] ?? null) && filled($c['identifier'] ?? null) && filled($c['secret'] ?? null);
    }

    public function cacheMinutes(): int
    {
        $minutes = (int) SettingsService::get('whmcs_cache_minutes', self::DEFAULT_CACHE_MINUTES);

        return $minutes >= 1 ? $minutes : self::DEFAULT_CACHE_MINUTES;
    }

    /** @return list<string> */
    public function heroTlds(): array
    {
        $tlds = SettingsService::get('whmcs_hero_tlds');

        if (! is_array($tlds) || $tlds === []) {
            return self::DEFAULT_HERO_TLDS;
        }

        return array_values(array_unique(array_map(self::normalizeTld(...), $tlds)));
    }

    public static function normalizeTld(string $tld): string
    {
        return '.' . ltrim(strtolower(trim($tld)), '.');
    }

    /* ───────────────────────── WHMCS API ───────────────────────── */

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function request(string $action, array $params = []): array
    {
        $c = $this->credentials();

        if (! $this->isConfigured()) {
            throw new RuntimeException('The WHMCS connection is not configured.');
        }

        $payload = array_filter([
            'identifier' => $c['identifier'],
            'secret' => $c['secret'],
            'accesskey' => $c['access_key'] ?? null,
            'action' => $action,
            'responsetype' => 'json',
            ...$params,
        ], fn ($v) => $v !== null && $v !== '');

        $response = Http::asForm()
            ->connectTimeout(4)
            ->timeout(8)
            ->post(rtrim($c['url'], '/') . '/includes/api.php', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('WHMCS responded with HTTP ' . $response->status() . '.');
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException('WHMCS returned an unexpected response. Check the WHMCS URL.');
        }

        if (($json['result'] ?? null) !== 'success') {
            throw new RuntimeException((string) ($json['message'] ?? 'WHMCS returned an error.'));
        }

        return $json;
    }

    /**
     * Fetch 1-year registration prices straight from WHMCS (no cache).
     *
     * @return array{currency: array{code: string, prefix: string, suffix: string}, tlds: array<string, float>}
     */
    public function fetchPricing(): array
    {
        $currencyId = $this->credentials()['currency_id'] ?? null;

        $json = $this->request('GetTLDPricing', filled($currencyId) ? ['currencyid' => (int) $currencyId] : []);

        $tlds = [];
        foreach (($json['pricing'] ?? []) as $tld => $info) {
            $amount = (float) ($info['register']['1'] ?? 0);
            if ($amount > 0) {
                $tlds[self::normalizeTld((string) $tld)] = $amount;
            }
        }

        $currency = $json['currency'] ?? [];

        return [
            'currency' => [
                'code' => (string) ($currency['code'] ?? ''),
                'prefix' => (string) ($currency['prefix'] ?? ''),
                'suffix' => (string) ($currency['suffix'] ?? ''),
            ],
            'tlds' => $tlds,
        ];
    }

    /* ───────────────────────── cached access ───────────────────────── */

    /**
     * Cached pricing. Keeps serving the last good copy if WHMCS is unreachable.
     *
     * @return array{currency: array{code: string, prefix: string, suffix: string}, tlds: array<string, float>}|null
     */
    public function pricing(): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        if ($fresh = Cache::get(self::FRESH_KEY)) {
            return $fresh;
        }

        if (Cache::has(self::BACKOFF_KEY)) {
            return Cache::get(self::STALE_KEY);
        }

        try {
            $data = $this->fetchPricing();
            Cache::put(self::FRESH_KEY, $data, now()->addMinutes($this->cacheMinutes()));
            Cache::forever(self::STALE_KEY, $data);

            return $data;
        } catch (Throwable $e) {
            Log::warning('WHMCS price sync failed: ' . $e->getMessage());
            Cache::put(self::BACKOFF_KEY, true, now()->addMinutes(5));

            return Cache::get(self::STALE_KEY);
        }
    }

    public static function flush(): void
    {
        Cache::forget(self::FRESH_KEY);
        Cache::forget(self::STALE_KEY);
        Cache::forget(self::BACKOFF_KEY);
    }

    /* ───────────────────────── homepage ───────────────────────── */

    /**
     * Prices for the hero line. Never throws: falls back to built-in prices.
     *
     * @return list<array{tld: string, price: string, live: bool}>
     */
    public function heroPrices(): array
    {
        try {
            $pricing = $this->pricing();
            $tlds = $this->heroTlds();
        } catch (Throwable $e) {
            Log::warning('Hero prices unavailable: ' . $e->getMessage());
            $pricing = null;
            $tlds = self::DEFAULT_HERO_TLDS;
        }

        $rows = [];
        foreach ($tlds as $tld) {
            if (isset($pricing['tlds'][$tld])) {
                $rows[] = ['tld' => $tld, 'price' => self::format($pricing['tlds'][$tld], $pricing['currency']), 'live' => true];
            } elseif (isset(self::FALLBACK_PRICES[$tld])) {
                $rows[] = ['tld' => $tld, 'price' => self::FALLBACK_PRICES[$tld], 'live' => false];
            }
        }

        return $rows;
    }

    /** @param array{prefix?: string, suffix?: string} $currency */
    public static function format(float $amount, array $currency): string
    {
        $prefix = $currency['prefix'] ?? '';
        $suffix = $prefix === '' ? ($currency['suffix'] ?? '') : '';

        return $prefix . number_format($amount, 2) . $suffix;
    }
}
