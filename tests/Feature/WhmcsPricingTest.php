<?php

namespace Tests\Feature;

use App\Filament\Pages\WhmcsConnection;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use App\Services\WhmcsService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class WhmcsPricingTest extends TestCase
{
    use RefreshDatabase;

    private function configure(): void
    {
        Setting::set('whmcs_url', 'https://my.example.com');
        Setting::set('whmcs_api_identifier', 'abc123');
        WhmcsService::storeSecret('whmcs_api_secret', 's3cret');
    }

    private function pricingResponse(): array
    {
        return [
            'result' => 'success',
            'currency' => ['id' => 1, 'code' => 'USD', 'prefix' => '$', 'suffix' => ' USD'],
            'pricing' => [
                'com' => ['register' => ['1' => '13.50', '2' => '27.00']],
                'com.bd' => ['register' => ['1' => '-1.00']],
                'xyz' => ['register' => ['1' => '2.49']],
            ],
        ];
    }

    public function test_hero_uses_built_in_prices_when_not_configured(): void
    {
        Http::fake();

        $prices = app(WhmcsService::class)->heroPrices();

        $this->assertSame(['.com', '.com.bd', '.xyz'], array_column($prices, 'tld'));
        $this->assertSame(['$12.99', '$9.50', '$2.99'], array_column($prices, 'price'));
        Http::assertNothingSent();

        $this->get('/')->assertOk()->assertSee('$12.99/yr', false);
    }

    public function test_hero_shows_live_whmcs_prices_and_skips_unavailable_ones(): void
    {
        $this->configure();
        Http::fake(['my.example.com/*' => Http::response($this->pricingResponse())]);

        $prices = app(WhmcsService::class)->heroPrices();

        $this->assertSame(['$13.50', '$9.50', '$2.49'], array_column($prices, 'price'));
        $this->assertSame([true, false, true], array_column($prices, 'live'));

        Http::assertSent(fn (Request $r) => $r->url() === 'https://my.example.com/includes/api.php'
            && $r['action'] === 'GetTLDPricing'
            && $r['identifier'] === 'abc123'
            && $r['secret'] === 's3cret'
            && $r['responsetype'] === 'json');

        $this->get('/')->assertOk()->assertSee('$13.50/yr', false)->assertSee('$2.49/yr', false);
        Http::assertSentCount(2); // 1 x GetTLDPricing (cached), 1 x GetProducts for the plan cards
    }

    public function test_secret_is_stored_encrypted(): void
    {
        $this->configure();

        $stored = SettingsService::get('whmcs_api_secret');

        $this->assertNotSame('s3cret', $stored);
        $this->assertTrue(WhmcsService::hasStoredSecret());
    }

    public function test_api_failure_falls_back_and_backs_off(): void
    {
        $this->configure();
        Http::fake(['my.example.com/*' => Http::response('Server Error', 500)]);

        $service = app(WhmcsService::class);

        $this->assertSame('$12.99', $service->heroPrices()[0]['price']);
        $this->assertSame('$12.99', $service->heroPrices()[0]['price']);
        Http::assertSentCount(1);
    }

    public function test_api_error_message_is_reported(): void
    {
        $this->configure();
        Http::fake(['my.example.com/*' => Http::response(['result' => 'error', 'message' => 'Invalid IP 1.2.3.4'])]);

        $this->expectExceptionMessage('Invalid IP 1.2.3.4');

        app(WhmcsService::class)->fetchPricing();
    }

    public function test_last_good_prices_survive_an_outage(): void
    {
        $this->configure();
        Http::fake(['my.example.com/*' => Http::sequence()
            ->push($this->pricingResponse())
            ->push('down', 503)]);

        $service = app(WhmcsService::class);
        $this->assertSame('$13.50', $service->heroPrices()[0]['price']);

        Cache::forget('whmcs_tld_pricing'); // cache expired, WHMCS now down

        $this->assertSame('$13.50', $service->heroPrices()[0]['price']);
    }

    public function test_hero_tld_setting_controls_order_and_selection(): void
    {
        $this->configure();
        Setting::set('whmcs_hero_tlds', ['xyz', '.COM']);
        Http::fake(['my.example.com/*' => Http::response($this->pricingResponse())]);

        $this->assertSame(['.xyz', '.com'], array_column(app(WhmcsService::class)->heroPrices(), 'tld'));
    }

    public function test_admin_page_saves_settings_and_never_stores_plain_secret(): void
    {
        Http::fake(['my.example.com/*' => Http::response($this->pricingResponse())]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());

        Livewire::test(WhmcsConnection::class)
            ->fillForm([
                'url' => 'https://my.example.com/',
                'api_identifier' => 'abc123',
                'api_secret' => 's3cret',
                'cache_minutes' => 60,
                'hero_tlds' => ['COM', '.xyz'],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saved and connected');

        $this->assertSame('https://my.example.com', SettingsService::get('whmcs_url'));
        $this->assertSame(['.com', '.xyz'], SettingsService::get('whmcs_hero_tlds'));
        $this->assertNotSame('s3cret', SettingsService::get('whmcs_api_secret'));
        $this->assertTrue(WhmcsService::hasStoredSecret());
        $this->assertSame('$13.50', app(WhmcsService::class)->heroPrices()[0]['price']);
    }

    public function test_admin_page_requires_https_url_and_a_secret_on_first_save(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());

        Livewire::test(WhmcsConnection::class)
            ->fillForm([
                'url' => 'http://my.example.com',
                'api_identifier' => 'abc123',
                'cache_minutes' => 60,
                'hero_tlds' => ['.com'],
            ])
            ->call('save')
            ->assertHasFormErrors(['url', 'api_secret']);
    }

    public function test_admin_page_can_test_connection_and_reports_failures(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());

        $form = [
            'url' => 'https://my.example.com',
            'api_identifier' => 'abc123',
            'api_secret' => 's3cret',
            'cache_minutes' => 60,
            'hero_tlds' => ['.com'],
        ];

        Http::fake(['my.example.com/*' => Http::sequence()
            ->push($this->pricingResponse())
            ->push(['result' => 'error', 'message' => 'Authentication Failed'])]);

        Livewire::test(WhmcsConnection::class)->fillForm($form)->call('testConnection')->assertNotified('Connection works');
        Livewire::test(WhmcsConnection::class)->fillForm($form)->call('testConnection')->assertNotified('Connection failed');

        $this->assertNull(SettingsService::get('whmcs_url'), 'testing must not save anything');
    }

    public function test_admin_page_is_reachable_and_connection_keys_are_hidden_from_generic_settings(): void
    {
        $this->actingAs(User::factory()->create());
        $this->configure();

        $this->get('/admin/whmcs-connection')->assertOk()->assertSee('WHMCS Connection')->assertDontSee('s3cret');
        $this->get('/admin/settings')->assertOk()->assertDontSee('whmcs_api_secret');
    }

    private function productsResponse(): array
    {
        return [
            'result' => 'success',
            'products' => ['product' => [
                ['pid' => 55, 'pricing' => ['BDT' => ['annually' => '3100.00'], 'USD' => ['annually' => '24.00']]],
                ['pid' => 56, 'pricing' => ['USD' => ['annually' => '40.00']]],
                ['pid' => 57, 'pricing' => ['BDT' => ['annually' => '-1.00']]],
            ]],
        ];
    }

    private function fakeWhmcs(): void
    {
        Http::fake(fn (Request $r) => Http::response($r['action'] === 'GetProducts' ? $this->productsResponse() : $this->pricingResponse()));
    }

    public function test_plan_price_prefers_bdt_then_usd_times_rate_then_the_typed_default(): void
    {
        $this->configure();
        $this->fakeWhmcs();

        $service = app(WhmcsService::class);

        $this->assertSame('3100', $service->planPrice('home', 0, 3000));   // BDT price
        $this->assertSame('5000', $service->planPrice('home', 1, 4200));   // USD 40 x rate 125
        $this->assertSame('6000', $service->planPrice('home', 2, 6000));   // unavailable -> default
        $this->assertSame('12000', $service->planPrice('home', 3, 12000)); // not returned -> default

        Http::assertSent(fn (Request $r) => $r['action'] === 'GetProducts' && $r['pid'] === '55,56,57,58,16,17,18,19');
    }

    public function test_pages_show_live_plan_prices_and_order_links_from_the_mapped_product(): void
    {
        $this->configure();
        Setting::set('whmcs_plan_pids', ['web_hosting.0' => 77, 'home.1' => null]);
        $this->fakeWhmcs();

        $home = $this->get('/')->assertOk();
        $home->assertSee('data-yearly-price="3100"', false)       // live
            ->assertSee('data-yearly-price="4200"', false)         // slot unmapped -> typed price
            ->assertSee('"price": 3100,', false)                   // structured data too
            ->assertSee('cart.php?a=add&amp;pid=55', false);

        $this->get('/web-hosting')->assertOk()->assertSee('cart.php?a=add&amp;pid=77', false);
    }

    public function test_plan_cards_keep_typed_prices_when_not_configured_or_when_whmcs_is_down(): void
    {
        Http::fake();
        $this->get('/')->assertOk()->assertSee('data-yearly-price="3000"', false);
        Http::assertNothingSent();

        $this->configure();
        Http::fake(['my.example.com/*' => Http::response('down', 500)]);
        $this->get('/')->assertOk()->assertSee('data-yearly-price="3000"', false);
    }

    public function test_order_links_without_a_known_product_stay_unchanged(): void
    {
        $this->get('/turbo-hosting')->assertOk()->assertSee('class="cloud-order-btn"', false);

        $this->assertSame('#', app(WhmcsService::class)->planUrl('turbo_hosting', 0));
        $this->assertSame('https://my.hostorio.com/cart.php?a=add&pid=9', (function () {
            Setting::set('whmcs_plan_pids', ['turbo_hosting.0' => 9]);
            WhmcsService::flush();

            return app(WhmcsService::class)->planUrl('turbo_hosting', 0);
        })());
    }

    public function test_admin_page_saves_the_plan_product_mapping(): void
    {
        $this->fakeWhmcs();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());

        Livewire::test(WhmcsConnection::class)
            ->fillForm([
                'url' => 'https://my.example.com',
                'api_identifier' => 'abc123',
                'api_secret' => 's3cret',
                'cache_minutes' => 60,
                'hero_tlds' => ['.com'],
                'plan_pids' => ['web_hosting' => [0 => '77'], 'home' => [0 => '55', 3 => null]],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saved and connected');

        $stored = SettingsService::get('whmcs_plan_pids');
        $this->assertSame(77, $stored['web_hosting.0']);
        $this->assertSame(55, $stored['home.0']);
        $this->assertNull($stored['home.3']);       // cleared on purpose
        $this->assertNull($stored['vps.0']);
    }
}
