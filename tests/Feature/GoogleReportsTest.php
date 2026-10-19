<?php

namespace Tests\Feature;

use App\Filament\Pages\GoogleConnection;
use App\Filament\Widgets\AnalyticsChannels;
use App\Filament\Widgets\AnalyticsOverview;
use App\Filament\Widgets\GoogleSetupWidget;
use App\Filament\Widgets\SearchConsoleOverview;
use App\Filament\Widgets\SearchTopPages;
use App\Filament\Widgets\SearchTopQueries;
use App\Models\Setting;
use App\Models\User;
use App\Services\GoogleReports;
use App\Services\SettingsService;
use Carbon\CarbonPeriod;
use Database\Seeders\ProductSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class GoogleReportsTest extends TestCase
{
    use RefreshDatabase;

    private static ?string $privateKey = null;
    private static ?string $publicKey = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$privateKey === null) {
            $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
            openssl_pkey_export($key, $private);
            self::$privateKey = $private;
            self::$publicKey = openssl_pkey_get_details($key)['key'];
        }
    }

    private function keyJson(array $overrides = []): string
    {
        return json_encode(array_merge([
            'type' => 'service_account',
            'project_id' => 'hostorio-site',
            'private_key_id' => 'abc',
            'private_key' => self::$privateKey,
            'client_email' => 'reports@hostorio-site.iam.gserviceaccount.com',
        ], $overrides));
    }

    private function connect(bool $searchConsole = true, bool $analytics = true): void
    {
        GoogleReports::storeServiceAccount($this->keyJson());
        Setting::set('google_gsc_property', $searchConsole ? 'sc-domain:hostorio.com' : '');
        Setting::set('google_ga4_property', $analytics ? '412345678' : '');
    }

    private function admin(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    private int $googleStatus = 200;
    private bool $googleFaked = false;

    /**
     * Google's token endpoint, Search Console and Analytics Data API, answering from the request.
     * Calling it again only changes the status: Http::fake keeps the first fake registered.
     */
    private function fakeGoogle(int $status = 200): void
    {
        $this->googleStatus = $status;

        if ($this->googleFaked) {
            return;
        }
        $this->googleFaked = true;

        Http::fake(function (Request $request) {
            $status = $this->googleStatus;

            if (str_starts_with($request->url(), 'https://oauth2.googleapis.com/token')) {
                return Http::response(['access_token' => 'ya29.test', 'expires_in' => 3600]);
            }

            if ($status !== 200) {
                return Http::response(['error' => ['code' => $status, 'message' => 'User does not have sufficient permission for this property.']], $status);
            }

            $body = $request->data();

            if (str_contains($request->url(), 'searchconsole.googleapis.com')) {
                $range = GoogleReports::range(7);

                return Http::response(['rows' => match ($body['dimensions'][0]) {
                    'date' => collect(CarbonPeriod::create($body['startDate'], $body['endDate']))->map(fn ($day) => [
                        'keys' => [$day->toDateString()],
                        'clicks' => $day->toDateString() >= $range['start'] ? 10 : 5,
                        'impressions' => $day->toDateString() >= $range['start'] ? 100 : 50,
                        'ctr' => 0.1,
                        'position' => $day->toDateString() >= $range['start'] ? 4 : 8,
                    ])->values()->all(),
                    'query' => [
                        ['keys' => ['web hosting bangladesh'], 'clicks' => 40, 'impressions' => 900, 'ctr' => 0.0444, 'position' => 3.24],
                        ['keys' => ['hostorio'], 'clicks' => 30, 'impressions' => 60, 'ctr' => 0.5, 'position' => 1],
                    ],
                    'page' => [
                        ['keys' => ['https://hostorio.com/web-hosting/'], 'clicks' => 12, 'impressions' => 300, 'ctr' => 0.04, 'position' => 3.44],
                        ['keys' => ['https://hostorio.com/'], 'clicks' => 50, 'impressions' => 800, 'ctr' => 0.0625, 'position' => 2.1],
                    ],
                }]);
            }

            if (str_contains($request->url(), 'analyticsdata.googleapis.com')) {
                if (count($body['dateRanges']) === 2) {
                    return Http::response(['rows' => [
                        ['dimensionValues' => [['value' => 'current']], 'metricValues' => [['value' => '120'], ['value' => '150'], ['value' => '400']]],
                        ['dimensionValues' => [['value' => 'previous']], 'metricValues' => [['value' => '100'], ['value' => '150'], ['value' => '500']]],
                    ]]);
                }

                $dimension = $body['dimensions'][0]['name'] ?? null;

                return Http::response(match ($dimension) {
                    'date' => ['rows' => [['dimensionValues' => [['value' => str_replace('-', '', GoogleReports::range(7)['end'])]], 'metricValues' => [['value' => '9'], ['value' => '11'], ['value' => '30']]]]],
                    'pagePath' => ['rows' => [['dimensionValues' => [['value' => '/web-hosting/']], 'metricValues' => [['value' => '210'], ['value' => '80']]]]],
                    'sessionDefaultChannelGroup' => ['rows' => [
                        ['dimensionValues' => [['value' => 'Organic Search']], 'metricValues' => [['value' => '75']]],
                        ['dimensionValues' => [['value' => 'Direct']], 'metricValues' => [['value' => '25']]],
                    ]],
                    default => ['rows' => [['metricValues' => [['value' => '120']]]]],
                });
            }

            return Http::response('unexpected', 500);
        });
    }

    public function test_the_server_signs_its_own_google_token(): void
    {
        $this->connect();
        $this->fakeGoogle();

        $this->assertSame('ya29.test', app(GoogleReports::class)->accessToken());

        Http::assertSent(function (Request $request) {
            if ($request->url() !== 'https://oauth2.googleapis.com/token') {
                return false;
            }

            [$header, $payload, $signature] = explode('.', $request['assertion']);
            $decode = fn (string $part) => base64_decode(strtr($part, '-_', '+/'));
            $claims = json_decode($decode($payload), true);

            return $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer'
                && openssl_verify("{$header}.{$payload}", $decode($signature), self::$publicKey, OPENSSL_ALGO_SHA256) === 1
                && $claims['iss'] === 'reports@hostorio-site.iam.gserviceaccount.com'
                && $claims['aud'] === 'https://oauth2.googleapis.com/token'
                && str_contains($claims['scope'], 'webmasters.readonly')
                && str_contains($claims['scope'], 'analytics.readonly');
        });
    }

    public function test_search_console_totals_compare_with_the_previous_period(): void
    {
        $this->connect();
        $this->fakeGoogle();

        $data = app(GoogleReports::class)->searchTotals(7);

        $this->assertSame(70.0, (float) $data['current']['clicks']);
        $this->assertSame(700.0, (float) $data['current']['impressions']);
        $this->assertEqualsWithDelta(10.0, $data['current']['ctr'], 0.001);
        $this->assertEqualsWithDelta(4.0, $data['current']['position'], 0.001);
        $this->assertSame(35.0, (float) $data['previous']['clicks']);
        $this->assertEqualsWithDelta(8.0, $data['previous']['position'], 0.001);
        $this->assertCount(7, $data['daily']);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/sites/sc-domain%3Ahostorio.com/searchAnalytics/query')
            && $r->hasHeader('Authorization', 'Bearer ya29.test'));

        // Cached: a second call does not ask Google again.
        $before = count(Http::recorded());
        app(GoogleReports::class)->searchTotals(7);
        $this->assertSame($before, count(Http::recorded()));
    }

    public function test_analytics_totals_and_tops(): void
    {
        $this->connect();
        $this->fakeGoogle();
        $reports = app(GoogleReports::class);

        $totals = $reports->analyticsTotals(7);
        $this->assertSame(['users' => 120.0, 'sessions' => 150.0, 'views' => 400.0], $totals['current']);
        $this->assertSame(500.0, $totals['previous']['views']);
        $this->assertCount(7, $totals['daily']);
        $this->assertSame(30.0, end($totals['daily'])['views']);

        $this->assertSame('/web-hosting/', $reports->analyticsTop('pagePath', 'screenPageViews', 'activeUsers', 7)[0]['key']);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'properties/412345678:runReport'));
    }

    public function test_errors_explain_the_fix_and_back_off(): void
    {
        $this->connect();
        $this->fakeGoogle(403);

        try {
            app(GoogleReports::class)->searchTotals(28);
            $this->fail('expected an error');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('User does not have sufficient permission', $e->getMessage());
            $this->assertStringContainsString('reports@hostorio-site.iam.gserviceaccount.com has access to the Search Console property', $e->getMessage());
        }

        $calls = count(Http::recorded());
        try {
            app(GoogleReports::class)->searchTotals(28);
        } catch (\RuntimeException) {
        }
        $this->assertSame($calls, count(Http::recorded()), 'waits 5 minutes before asking again');
    }

    public function test_last_good_numbers_survive_an_outage(): void
    {
        $this->connect();
        $this->fakeGoogle();
        $this->assertSame(70.0, (float) app(GoogleReports::class)->searchTotals(7)['current']['clicks']);

        $this->travel(GoogleReports::DEFAULT_CACHE_MINUTES + 1)->minutes(); // the cache has expired...
        $this->fakeGoogle(500);                                               // ...and Google is down

        $this->assertSame(70.0, (float) app(GoogleReports::class)->searchTotals(7)['current']['clicks']);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'searchconsole'));
    }

    public function test_connection_page_validates_saves_encrypted_and_tests(): void
    {
        $this->admin();
        $this->fakeGoogle();

        $this->get('/admin/google-connection')->assertOk()->assertSee('One-time setup in Google');

        Livewire::test(GoogleConnection::class)
            ->fillForm(['service_account' => '{not json', 'gsc_property' => 'sc-domain:hostorio.com', 'cache_minutes' => 180])
            ->call('save')
            ->assertHasFormErrors(['service_account']);

        Livewire::test(GoogleConnection::class)
            ->fillForm(['service_account' => $this->keyJson(['type' => 'authorized_user']), 'cache_minutes' => 180])
            ->call('save')
            ->assertHasFormErrors(['service_account']);

        Livewire::test(GoogleConnection::class)
            ->fillForm(['service_account' => $this->keyJson(), 'ga4_property' => 'G-PJSW407GJS', 'cache_minutes' => 180])
            ->call('save')
            ->assertHasFormErrors(['ga4_property']);

        Livewire::test(GoogleConnection::class)
            ->fillForm(['service_account' => $this->keyJson(), 'gsc_property' => 'sc-domain:hostorio.com', 'ga4_property' => '412345678', 'cache_minutes' => 180])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saved and connected');

        $stored = (string) SettingsService::get('google_service_account');
        $this->assertStringNotContainsString('PRIVATE KEY', $stored);
        $this->assertStringNotContainsString('gserviceaccount', $stored);
        $this->assertSame('reports@hostorio-site.iam.gserviceaccount.com', app(GoogleReports::class)->clientEmail());

        $this->get('/admin/google-connection')->assertSee('reports@hostorio-site.iam.gserviceaccount.com');
        $this->get('/admin/settings')->assertDontSee('google_service_account');
    }

    public function test_dashboard_asks_to_connect_until_google_is_set_up(): void
    {
        $this->admin();

        $this->get('/admin')->assertOk()->assertSee('Connect Google');
        $this->assertTrue(GoogleSetupWidget::canView());
        $this->assertFalse(SearchConsoleOverview::canView());
        $this->assertFalse(AnalyticsOverview::canView());

        $this->connect(analytics: false);
        $this->assertFalse(GoogleSetupWidget::canView());
        $this->assertTrue(SearchConsoleOverview::canView());
        $this->assertFalse(AnalyticsOverview::canView());
    }

    public function test_dashboard_widgets_show_google_numbers(): void
    {
        $this->admin();
        $this->connect();
        $this->fakeGoogle();

        Livewire::test(SearchConsoleOverview::class, ['pageFilters' => ['period' => 7]])
            ->assertSee('Google Search · last 7 days')
            ->assertSee('Clicks')->assertSee('70')
            ->assertSee('+100% vs previous 7 days')
            ->assertSee('Average position')->assertSee('4.0')
            ->assertSee('-4.0 vs previous 7 days');

        Livewire::test(SearchTopQueries::class, ['pageFilters' => ['period' => 7]])->assertSee('web hosting bangladesh')->assertSee('3.2');
        Livewire::test(SearchTopPages::class, ['pageFilters' => ['period' => 7]])->assertSee('/web-hosting/');
        Livewire::test(AnalyticsOverview::class, ['pageFilters' => ['period' => 7]])->assertSee('Users')->assertSee('120')->assertSee('+20% vs previous 7 days');
        Livewire::test(AnalyticsChannels::class, ['pageFilters' => ['period' => 7]])->assertSee('Organic Search')->assertSee('75%');

        $this->fakeGoogle(403);
        Cache::flush();
        Livewire::test(SearchTopQueries::class, ['pageFilters' => ['period' => 90]])->assertSee('sufficient permission');
    }

    public function test_page_seo_list_shows_google_numbers_per_page(): void
    {
        $this->seed(ProductSeeder::class);
        $this->admin();
        $this->connect(analytics: false);
        $this->fakeGoogle();

        $this->get('/admin/seo-pages')->assertOk()
            ->assertSee('Google, 28 days')
            ->assertSee('12 clicks · position 3.4')
            ->assertSee('50 clicks · position 2.1');
    }

    public function test_disconnect_removes_the_key(): void
    {
        $this->admin();
        $this->connect();

        Livewire::test(GoogleConnection::class)->callAction('disconnect')->assertNotified('Disconnected');

        $this->assertNull(app(GoogleReports::class)->clientEmail());
        $this->assertFalse(app(GoogleReports::class)->isConfigured());
    }
}
