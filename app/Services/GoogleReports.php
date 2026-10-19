<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Google Search Console and Google Analytics 4 numbers for the dashboard
 * (Admin → Google Connection). Uses a service account: the server signs a
 * token itself, so nobody has to log in to Google from the admin.
 */
class GoogleReports
{
    public const DEFAULT_CACHE_MINUTES = 180;
    public const PERIODS = [7 => 'Last 7 days', 28 => 'Last 28 days', 90 => 'Last 3 months'];

    private const SCOPES = 'https://www.googleapis.com/auth/webmasters.readonly https://www.googleapis.com/auth/analytics.readonly';

    /** @param array<string, mixed>|null $override  credentials to test without saving */
    public function __construct(private ?array $override = null)
    {
    }

    public static function withSettings(array $override): self
    {
        return new self($override);
    }

    /* ───────────────────────── settings ───────────────────────── */

    /**
     * Decode and check a downloaded service account key file.
     *
     * @return array<string, mixed>
     */
    public static function parseServiceAccount(?string $json): array
    {
        $data = json_decode(trim((string) $json), true);

        if (! is_array($data)) {
            throw new RuntimeException('This is not valid JSON. Paste the whole contents of the downloaded key file.');
        }

        if (($data['type'] ?? null) !== 'service_account' || empty($data['client_email']) || empty($data['private_key'])) {
            throw new RuntimeException('This is not a service account key. Download a JSON key from Google Cloud → IAM & Admin → Service accounts → Keys.');
        }

        if (! openssl_pkey_get_private($data['private_key'])) {
            throw new RuntimeException('The private key in this file cannot be read.');
        }

        return $data;
    }

    public static function storeServiceAccount(string $json): void
    {
        $data = self::parseServiceAccount($json);

        Setting::set('google_service_account', Crypt::encryptString(json_encode([
            'client_email' => $data['client_email'],
            'private_key' => $data['private_key'],
            'project_id' => $data['project_id'] ?? null,
        ])));
    }

    /** @return array{client_email: string, private_key: string}|null */
    public function serviceAccount(): ?array
    {
        if (isset($this->override['service_account'])) {
            return $this->override['service_account'];
        }

        $stored = SettingsService::get('google_service_account');

        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            $data = json_decode(Crypt::decryptString($stored), true);
        } catch (Throwable) {
            return null; // e.g. APP_KEY changed since it was saved
        }

        return is_array($data) && isset($data['client_email'], $data['private_key']) ? $data : null;
    }

    public function clientEmail(): ?string
    {
        return $this->serviceAccount()['client_email'] ?? null;
    }

    public function searchConsoleProperty(): string
    {
        return trim((string) ($this->override['gsc_property'] ?? SettingsService::get('google_gsc_property', '')));
    }

    public function analyticsProperty(): string
    {
        return preg_replace('/\D/', '', (string) ($this->override['ga4_property'] ?? SettingsService::get('google_ga4_property', '')));
    }

    public function hasSearchConsole(): bool
    {
        return $this->serviceAccount() !== null && $this->searchConsoleProperty() !== '';
    }

    public function hasAnalytics(): bool
    {
        return $this->serviceAccount() !== null && $this->analyticsProperty() !== '';
    }

    public function isConfigured(): bool
    {
        return $this->hasSearchConsole() || $this->hasAnalytics();
    }

    private function cacheMinutes(): int
    {
        $minutes = (int) SettingsService::get('google_cache_minutes', self::DEFAULT_CACHE_MINUTES);

        return $minutes >= 5 ? $minutes : self::DEFAULT_CACHE_MINUTES;
    }

    /* ───────────────────────── Google API ───────────────────────── */

    public function accessToken(): string
    {
        $account = $this->serviceAccount() ?? throw new RuntimeException('No Google service account key is saved.');
        $cacheKey = 'google_token_' . md5($account['client_email'] . $account['private_key']);

        if ($this->override === null && ($token = Cache::get($cacheKey))) {
            return $token;
        }

        $now = time();
        $segments = [
            self::base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
            self::base64url(json_encode([
                'iss' => $account['client_email'],
                'scope' => self::SCOPES,
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ])),
        ];

        if (! openssl_sign(implode('.', $segments), $signature, $account['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('The private key could not sign the request.');
        }

        $response = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => implode('.', [...$segments, self::base64url($signature)]),
        ]);

        if (! $response->successful() || ! $response->json('access_token')) {
            throw new RuntimeException('Google refused the key: ' . ($response->json('error_description') ?? $response->json('error') ?? 'HTTP ' . $response->status()));
        }

        if ($this->override === null) {
            Cache::put($cacheKey, $response->json('access_token'), now()->addSeconds(max(60, (int) $response->json('expires_in', 3600) - 120)));
        }

        return $response->json('access_token');
    }

    /** @return array<string, mixed> */
    private function post(string $url, array $body, string $service): array
    {
        $response = Http::withToken($this->accessToken())->acceptJson()->timeout(15)->post($url, $body);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?? ('HTTP ' . $response->status());

            if ($response->status() === 403) {
                $message .= " Check that {$this->clientEmail()} has access to the {$service} property.";
            }

            throw new RuntimeException("{$service}: {$message}");
        }

        return $response->json() ?? [];
    }

    /** @return list<array{keys: list<string>, clicks: float, impressions: float, ctr: float, position: float}> */
    public function searchQuery(string $start, string $end, array $dimensions, int $limit = 1000): array
    {
        $property = $this->searchConsoleProperty();

        return $this->post(
            'https://searchconsole.googleapis.com/webmasters/v3/sites/' . rawurlencode($property) . '/searchAnalytics/query',
            ['startDate' => $start, 'endDate' => $end, 'dimensions' => $dimensions, 'rowLimit' => $limit, 'dataState' => 'all'],
            'Search Console',
        )['rows'] ?? [];
    }

    /** @return array<string, mixed> */
    public function analyticsReport(array $body): array
    {
        return $this->post(
            'https://analyticsdata.googleapis.com/v1beta/properties/' . $this->analyticsProperty() . ':runReport',
            $body,
            'Google Analytics',
        );
    }

    /* ───────────────────────── reports ───────────────────────── */

    /** @return array{start: string, end: string, previousStart: string, previousEnd: string} */
    public static function range(int $days): array
    {
        $end = CarbonImmutable::yesterday();
        $start = $end->subDays($days - 1);

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'previousStart' => $start->subDays($days)->toDateString(),
            'previousEnd' => $start->subDay()->toDateString(),
        ];
    }

    /**
     * Clicks, impressions, CTR and average position for the period and the one before, plus daily numbers.
     *
     * @return array{current: array<string, float>, previous: array<string, float>, daily: array<string, array{clicks: float, impressions: float}>}
     */
    public function searchTotals(int $days): array
    {
        return $this->remember("gsc_totals_{$days}", function () use ($days) {
            $range = self::range($days);
            $rows = collect($this->searchQuery($range['previousStart'], $range['end'], ['date']));

            $sum = function ($rows): array {
                $clicks = $rows->sum('clicks');
                $impressions = $rows->sum('impressions');

                return [
                    'clicks' => $clicks,
                    'impressions' => $impressions,
                    'ctr' => $impressions > 0 ? $clicks / $impressions * 100 : 0,
                    'position' => $impressions > 0 ? $rows->sum(fn ($r) => $r['position'] * $r['impressions']) / $impressions : 0,
                ];
            };

            $current = $rows->filter(fn ($r) => $r['keys'][0] >= $range['start']);
            $daily = [];
            foreach (CarbonPeriod::create($range['start'], $range['end']) as $date) {
                $row = $current->first(fn ($r) => $r['keys'][0] === $date->toDateString());
                $daily[$date->toDateString()] = ['clicks' => $row['clicks'] ?? 0, 'impressions' => $row['impressions'] ?? 0];
            }

            return [
                'current' => $sum($current),
                'previous' => $sum($rows->filter(fn ($r) => $r['keys'][0] < $range['start'])),
                'daily' => $daily,
            ];
        });
    }

    /** @return list<array{key: string, clicks: float, impressions: float, ctr: float, position: float}> */
    public function searchTop(string $dimension, int $days, int $limit): array
    {
        return $this->remember("gsc_top_{$dimension}_{$days}_{$limit}", function () use ($dimension, $days, $limit) {
            $range = self::range($days);

            return array_map(fn ($r) => [
                'key' => $r['keys'][0],
                'clicks' => $r['clicks'],
                'impressions' => $r['impressions'],
                'ctr' => $r['ctr'] * 100,
                'position' => $r['position'],
            ], $this->searchQuery($range['start'], $range['end'], [$dimension], $limit));
        });
    }

    /** Search numbers for one page address (path like "/web-hosting/"), from the cached top-pages report. */
    public function searchForPath(string $path, int $days = 28): ?array
    {
        foreach ($this->searchTop('page', $days, 500) as $row) {
            if (rtrim((string) parse_url($row['key'], PHP_URL_PATH), '/') === rtrim($path, '/')) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Users, sessions and page views for the period and the one before, plus daily numbers.
     *
     * @return array{current: array<string, float>, previous: array<string, float>, daily: array<string, array<string, float>>}
     */
    public function analyticsTotals(int $days): array
    {
        return $this->remember("ga4_totals_{$days}", function () use ($days) {
            $range = self::range($days);
            $metrics = [['name' => 'activeUsers'], ['name' => 'sessions'], ['name' => 'screenPageViews']];

            $totals = $this->analyticsReport([
                'dateRanges' => [
                    ['startDate' => $range['start'], 'endDate' => $range['end'], 'name' => 'current'],
                    ['startDate' => $range['previousStart'], 'endDate' => $range['previousEnd'], 'name' => 'previous'],
                ],
                'metrics' => $metrics,
            ]);

            $result = ['current' => ['users' => 0, 'sessions' => 0, 'views' => 0], 'previous' => ['users' => 0, 'sessions' => 0, 'views' => 0]];
            foreach ($totals['rows'] ?? [] as $row) {
                $name = $row['dimensionValues'][0]['value'] ?? 'current';
                $result[$name] = self::metricValues($row);
            }

            $daily = [];
            foreach (CarbonPeriod::create($range['start'], $range['end']) as $date) {
                $daily[$date->format('Ymd')] = ['users' => 0, 'sessions' => 0, 'views' => 0];
            }
            $byDay = $this->analyticsReport([
                'dateRanges' => [['startDate' => $range['start'], 'endDate' => $range['end']]],
                'dimensions' => [['name' => 'date']],
                'metrics' => $metrics,
            ]);
            foreach ($byDay['rows'] ?? [] as $row) {
                $daily[$row['dimensionValues'][0]['value']] = self::metricValues($row);
            }
            ksort($daily);

            return $result + ['daily' => $daily];
        });
    }

    /** @return list<array{key: string, value: float, secondary: float}> */
    public function analyticsTop(string $dimension, string $metric, ?string $secondaryMetric, int $days, int $limit = 10): array
    {
        return $this->remember("ga4_top_{$dimension}_{$metric}_{$days}_{$limit}", function () use ($dimension, $metric, $secondaryMetric, $days, $limit) {
            $range = self::range($days);
            $metrics = array_values(array_filter([['name' => $metric], $secondaryMetric ? ['name' => $secondaryMetric] : null]));

            $report = $this->analyticsReport([
                'dateRanges' => [['startDate' => $range['start'], 'endDate' => $range['end']]],
                'dimensions' => [['name' => $dimension]],
                'metrics' => $metrics,
                'orderBys' => [['metric' => ['metricName' => $metric], 'desc' => true]],
                'limit' => $limit,
            ]);

            return array_map(fn ($row) => [
                'key' => $row['dimensionValues'][0]['value'] ?? '',
                'value' => (float) ($row['metricValues'][0]['value'] ?? 0),
                'secondary' => (float) ($row['metricValues'][1]['value'] ?? 0),
            ], $report['rows'] ?? []);
        });
    }

    /** @return array{users: float, sessions: float, views: float} */
    private static function metricValues(array $row): array
    {
        $values = array_map(fn ($v) => (float) ($v['value'] ?? 0), $row['metricValues'] ?? []);

        return ['users' => $values[0] ?? 0, 'sessions' => $values[1] ?? 0, 'views' => $values[2] ?? 0];
    }

    /* ───────────────────────── cache ───────────────────────── */

    /**
     * Fresh cache, then a 5-minute back-off after a failure, then the last good
     * copy. Throws when there is nothing to show, so widgets can say why.
     */
    private function remember(string $name, \Closure $fetch): mixed
    {
        $scope = md5(implode('|', [$this->clientEmail(), $this->searchConsoleProperty(), $this->analyticsProperty(), SettingsService::get('google_cache_version', 0)]));
        $fresh = "google_{$scope}_{$name}";
        $stale = "{$fresh}_last_good";
        $backoff = "{$fresh}_backoff";

        if (($cached = Cache::get($fresh)) !== null) {
            return $cached;
        }

        if (($error = Cache::get($backoff)) !== null) {
            return Cache::get($stale) ?? throw new RuntimeException($error);
        }

        try {
            $data = $fetch();
            Cache::put($fresh, $data, now()->addMinutes($this->cacheMinutes()));
            Cache::forever($stale, $data);

            return $data;
        } catch (Throwable $e) {
            Log::warning("Google report {$name} failed: " . $e->getMessage());
            Cache::put($backoff, $e->getMessage(), now()->addMinutes(5));

            return Cache::get($stale) ?? throw $e;
        }
    }

    /** Forget cached reports, e.g. after the connection settings change. */
    public static function flush(): void
    {
        Setting::set('google_cache_version', (int) SettingsService::get('google_cache_version', 0) + 1);
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
