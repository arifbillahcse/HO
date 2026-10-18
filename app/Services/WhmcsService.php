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

    /**
     * Plan cards per page, in the order they appear. Default WHMCS product IDs
     * are the ones already used by the order buttons; the admin can change them.
     *
     * @var array<string, array{label: string, plans: list<array{0: string, 1: int|null}>}>
     */
    public const PLANS = [
        'home' => ['label' => 'Home page', 'plans' => [['Starter', 55], ['Lite Starter', 56], ['Standard', 57], ['Advanced', 58]]],
        'web_hosting' => ['label' => 'Web Hosting', 'plans' => [['Starter', 55], ['Lite Starter', 56], ['Standard', 57], ['Advanced', 58]]],
        'turbo_hosting' => ['label' => 'Turbo Hosting', 'plans' => [['Turbo Plus', null], ['Turbo Power', null], ['Turbo Enterprise', null], ['Turbo Rapid', null]]],
        'reseller_hosting' => ['label' => 'Reseller Hosting', 'plans' => [['R-Starter', null], ['R-Standard', null], ['R-Advanced', null], ['R-Advanced Pro', null]]],
        'student_hosting' => ['label' => 'Student Hosting', 'plans' => [['SH Plan 1', null], ['SH Plan 2', null], ['SH Plan 3', null]]],
        'vps' => ['label' => 'VPS', 'plans' => [['Enhanced VPS', null], ['Premium VPS', null], ['Elite VPS', null], ['Ultimate VPS', null]]],
        'business_email' => ['label' => 'Business Email', 'plans' => [['Starter Mailboxes', null], ['Pro Mailboxes', null], ['Ultimate Mailboxes', null]]],
        'offer' => ['label' => 'Offer', 'plans' => [['2GB + .SHOP domain', 16], ['5GB + .XYZ', 17], ['10GB + .COM', 18], ['20GB + .COM', 19]]],
    ];

    private const TLD_CACHE = 'tld_pricing';
    private const PLAN_CACHE = 'plan_prices';

    /**
     * @param  array{url?: ?string, identifier?: ?string, secret?: ?string, access_key?: ?string, currency_id?: int|string|null}|null  $credentials
     */
    private ?array $planPriceMemo = null;

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
     * Fresh cache, then a 5-minute back-off after a failure, then the last
     * good copy if WHMCS is unreachable.
     */
    private function remember(string $name, \Closure $fetch): mixed
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $fresh = "whmcs_{$name}";
        $stale = "whmcs_{$name}_last_good";
        $backoff = "whmcs_{$name}_backoff";

        if (($cached = Cache::get($fresh)) !== null) {
            return $cached;
        }

        if (Cache::has($backoff)) {
            return Cache::get($stale);
        }

        try {
            $data = $fetch();
            Cache::put($fresh, $data, now()->addMinutes($this->cacheMinutes()));
            Cache::forever($stale, $data);

            return $data;
        } catch (Throwable $e) {
            Log::warning("WHMCS {$name} sync failed: " . $e->getMessage());
            Cache::put($backoff, true, now()->addMinutes(5));

            return Cache::get($stale);
        }
    }

    /**
     * Cached domain pricing.
     *
     * @return array{currency: array{code: string, prefix: string, suffix: string}, tlds: array<string, float>}|null
     */
    public function pricing(): ?array
    {
        return $this->remember(self::TLD_CACHE, fn () => $this->fetchPricing());
    }

    /**
     * Cached yearly BDT prices keyed by WHMCS product ID.
     *
     * @return array<int, float>|null
     */
    public function planPrices(): ?array
    {
        return $this->planPriceMemo ??= $this->remember(self::PLAN_CACHE, fn () => $this->fetchPlanPrices());
    }

    public static function flush(): void
    {
        foreach ([self::TLD_CACHE, self::PLAN_CACHE] as $name) {
            Cache::forget("whmcs_{$name}");
            Cache::forget("whmcs_{$name}_last_good");
            Cache::forget("whmcs_{$name}_backoff");
        }

        app()->forgetInstance(self::class);
    }

    /* ───────────────────────── hosting plans ───────────────────────── */

    /**
     * Effective WHMCS product ID for a plan card (admin value, else the default).
     */
    public function planPid(string $page, int $slot): ?int
    {
        $stored = SettingsService::get('whmcs_plan_pids');
        $key = "{$page}.{$slot}";

        if (is_array($stored) && array_key_exists($key, $stored)) {
            return filled($stored[$key]) ? (int) $stored[$key] : null;
        }

        return self::PLANS[$page]['plans'][$slot][1] ?? null;
    }

    /** @return list<int> */
    public function mappedPids(): array
    {
        $pids = [];
        foreach (self::PLANS as $page => $info) {
            foreach (array_keys($info['plans']) as $slot) {
                if ($pid = $this->planPid($page, $slot)) {
                    $pids[] = $pid;
                }
            }
        }

        return array_values(array_unique($pids));
    }

    /**
     * Yearly price in BDT per product ID, taken from WHMCS (no cache). Uses the
     * BDT price when WHMCS has one, else the USD price times the site's rate.
     *
     * @param  list<int>|null  $pids
     * @return array<int, float>
     */
    public function fetchPlanPrices(?array $pids = null): array
    {
        $pids ??= $this->mappedPids();

        if ($pids === []) {
            return [];
        }

        $json = $this->request('GetProducts', ['pid' => implode(',', $pids)]);

        $products = $json['products']['product'] ?? [];
        if (isset($products['pid'])) {
            $products = [$products];
        }

        $rate = (float) SettingsService::get('exchange_rate', 125);
        $prices = [];

        foreach ($products as $product) {
            $pricing = $product['pricing'] ?? [];
            $yearly = (float) ($pricing['BDT']['annually'] ?? 0);

            if ($yearly <= 0) {
                $yearly = (float) ($pricing['USD']['annually'] ?? 0) * $rate;
            }

            if ($yearly > 0) {
                $prices[(int) $product['pid']] = round($yearly, 2);
            }
        }

        return $prices;
    }

    /** Live yearly BDT price for a plan card, or $default. Never throws. */
    public function planPrice(string $page, int $slot, float | int $default): string
    {
        $price = $default;

        try {
            $pid = $this->planPid($page, $slot);
            $live = $pid ? ($this->planPrices() ?? []) : [];

            if ($pid && isset($live[$pid])) {
                $price = $live[$pid];
            }
        } catch (Throwable $e) {
            Log::warning('Plan price unavailable: ' . $e->getMessage());
        }

        return rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.');
    }

    /** Order link for a plan card: the mapped WHMCS product, else $default. */
    public function planUrl(string $page, int $slot, string $default = '#'): string
    {
        $pid = $this->planPid($page, $slot);

        if (! $pid) {
            return $default;
        }

        if (preg_match('/([?&])pid=\d+/', $default)) {
            return preg_replace('/([?&])pid=\d+/', '${1}pid=' . $pid, $default, 1);
        }

        $base = rtrim((string) (SettingsService::get('client_area_url') ?: 'https://my.hostorio.com/'), '/');

        return "{$base}/cart.php?a=add&pid={$pid}";
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
