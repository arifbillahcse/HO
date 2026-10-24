<?php

namespace Tests\Feature;

use App\Models\ComparisonRow;
use App\Models\Product;
use App\Models\Setting;
use App\Services\WhmcsService;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductCardsTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        'home' => '/', 'web_hosting' => '/web-hosting/', 'turbo_hosting' => '/turbo-hosting/',
        'reseller_hosting' => '/reseller-hosting/', 'student_hosting' => '/student-hosting/', 'vps' => '/vps/',
        'business_email' => '/business-email/', 'offer' => '/offer/',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    private function configureWhmcs(): void
    {
        Setting::set('whmcs_url', 'https://my.example.com');
        Setting::set('whmcs_api_identifier', 'abc123');
        WhmcsService::storeSecret('whmcs_api_secret', 's3cret');
    }

    private function fakeWhmcs(int $status = 200): void
    {
        Http::fake(fn (Request $r) => $status !== 200
            ? Http::response('down', $status)
            : Http::response($r['action'] === 'GetProducts'
                ? ['result' => 'success', 'products' => ['product' => [
                    ['pid' => 55, 'paytype' => 'recurring', 'pricing' => ['BDT' => ['annually' => '3100.00', 'monthly' => '350.00']]],
                    ['pid' => 56, 'paytype' => 'recurring', 'pricing' => ['USD' => ['annually' => '40.00', 'monthly' => '-1.00']]],
                    ['pid' => 57, 'pricing' => ['BDT' => ['annually' => '-1.00', 'monthly' => '600.00']]],
                    ['pid' => 58, 'paytype' => 'onetime', 'pricing' => ['BDT' => ['annually' => '12500.00', 'monthly' => '12500.00']]],
                ]]]
                : ['result' => 'success', 'currency' => ['code' => 'USD', 'prefix' => '$'], 'pricing' => []]));
    }

    public function test_every_plan_page_renders_its_products_from_the_admin(): void
    {
        foreach (self::PAGES as $page => $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $products = Product::onPage($page)->get();

            $this->assertSame($products->count(), substr_count($html, 'class="cloud-card'), $page);
            foreach ($products as $product) {
                $this->assertStringContainsString('<h3 class="cloud-plan-name">' . e($product->name) . '</h3>', $html, $page);
            }
            $this->assertStringNotContainsString('data-i18n="pricing.plans.', $html, "{$page}: product text must not be translated");
        }
    }

    public function test_admin_edits_show_on_the_website(): void
    {
        $starter = Product::onPage('web_hosting')->first();
        $starter->update([
            'name' => 'Starter Plus',
            'is_popular' => true,
            'badge_text' => 'Best Value',
            'button_text' => 'Buy now',
            'features' => [['value' => '10GB', 'text' => 'NVMe Storage', 'is_new' => true]],
        ]);
        Product::onPage('web_hosting')->skip(3)->first()->update(['is_visible' => false]);

        $html = $this->get('/web-hosting/')->assertOk()->getContent();

        $this->assertStringContainsString('<h3 class="cloud-plan-name">Starter Plus</h3>', $html);
        $this->assertStringContainsString('<div class="cloud-popular-badge">Best Value</div>', $html);
        $this->assertStringContainsString('<strong>10GB</strong> NVMe Storage <span class="feature-badge new">NEW</span>', $html);
        $this->assertStringContainsString('>Buy now</a>', $html);
        $this->assertStringNotContainsString('<h3 class="cloud-plan-name">Advanced</h3>', $html);
        $this->assertSame(3, substr_count($html, 'class="cloud-card'));
    }

    public function test_admin_order_controls_the_card_order(): void
    {
        [$first, $second] = Product::onPage('turbo_hosting')->take(2)->get()->all();
        $first->update(['sort_order' => 10]);

        $html = $this->get('/turbo-hosting/')->getContent();

        $this->assertLessThan(strpos($html, e($first->name) . '</h3>'), strpos($html, e($second->name) . '</h3>'));
    }

    public function test_cards_use_live_whmcs_prices_with_fallbacks(): void
    {
        $this->configureWhmcs();
        $this->fakeWhmcs();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-yearly-price="3100"', $html);   // BDT price from WHMCS
        $this->assertStringContainsString('data-yearly-price="5000"', $html);   // USD 40 x rate 125
        $this->assertStringContainsString('data-yearly-price="6000"', $html);   // unavailable -> fallback
        $this->assertStringContainsString('data-yearly-price="12500"', $html);
        Http::assertSent(fn (Request $r) => $r['action'] === 'GetProducts' && $r['pid'] === '16,17,18,19,55,56,57,58');
    }

    public function test_monthly_view_uses_real_monthly_prices_from_whmcs(): void
    {
        $this->configureWhmcs();
        $this->fakeWhmcs();

        $html = $this->get('/web-hosting/')->assertOk()->getContent();

        $this->assertStringContainsString('id="billingToggle"', $html);
        $this->assertSame(1, substr_count($html, 'data-monthly-price='), 'only pid 55 sells monthly (56 has no monthly cycle, 57 has no yearly price, 58 is one-time)');
        $this->assertStringContainsString('data-yearly-price="3100" data-monthly-price="350"', $html);
        $this->assertStringContainsString('data-yearly-url="https://my.hostorio.com/cart.php?a=add&amp;pid=55&amp;billingcycle=annually" data-monthly-url="https://my.hostorio.com/cart.php?a=add&amp;pid=55&amp;billingcycle=monthly"', $html);
        $this->assertStringContainsString('<span class="price-period">or ৳350/mo billed monthly</span>', $html);
        $this->assertStringContainsString('data-period-year="pricing.perYear|/yr" data-period-month="pricing.perMonth|/mo">/yr</span>', $html);

        // Crossed-out price = 12 × the WHMCS monthly price (350 × 12), only on the plan that has one.
        $this->assertSame(1, substr_count($html, 'class="cloud-price-original"'));
        $this->assertStringContainsString('<span class="orig-amount">4,200</span>', $html);
        $this->assertStringContainsString('data-i18n="pricing.save30">Up to 30%!</span>', $html);
        $this->assertStringContainsString('data-i18n="pricing.saveBadge">Up to 30%!</span>', $html);
    }

    public function test_hosting_pages_always_offer_monthly_and_yearly_billing(): void
    {
        foreach (['/web-hosting/', '/turbo-hosting/', '/reseller-hosting/', '/student-hosting/', '/vps/', '/business-email/'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('id="billingToggle"', $html, $url);
            $this->assertStringNotContainsString('data-monthly-price', $html, "{$url}: no monthly price is made up");
            $this->assertStringNotContainsString('cloud-price-original', $html, "{$url}: no made-up crossed-out price");
        }

        foreach (['/', '/offer/'] as $url) {
            $this->get($url)->assertDontSee('id="billingToggle"', false);
        }

        // Cards start on the yearly label (VPS used to show "/mo" next to the yearly price).
        $this->get('/vps/')->assertSee('data-i18n="vpsPricing.perYear"', false);

        Product::onPage('turbo_hosting')->first()->update(['monthly_price' => 1500]);

        $this->get('/turbo-hosting/')
            ->assertSee('data-monthly-price="1500"', false)
            ->assertSee('or ৳1,500/mo billed monthly');
    }

    public function test_admin_lists_live_prices_and_plans_missing_a_monthly_price(): void
    {
        $this->configureWhmcs();
        $this->fakeWhmcs();
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $this->actingAs(\App\Models\User::factory()->create());

        \Livewire\Livewire::test(\App\Filament\Resources\Products\Pages\ListProducts::class)
            ->assertOk()
            ->assertSee('৳3,100/yr')   // live WHMCS yearly price, not the 3,000 fallback
            ->assertSee('৳350/mo')
            ->assertSee('Missing')
            ->assertSee('Yearly only');

        $this->get('/admin/launch-checklist')->assertSee('plans have no monthly price')->assertSee('Reseller Hosting (R-Starter');
    }

    public function test_cards_keep_fallback_prices_when_whmcs_is_down(): void
    {
        $this->configureWhmcs();
        $this->fakeWhmcs(500);

        $this->get('/')->assertOk()->assertSee('data-yearly-price="3000"', false);
    }

    public function test_order_links_follow_the_whmcs_product_id(): void
    {
        $this->get('/')->assertSee('href="https://my.hostorio.com/cart.php?a=add&amp;pid=55"', false);
        $this->get('/vps/')->assertSee('<a href="https://my.hostorio.com/cart.php" class="cloud-order-btn">DEPLOY NOW</a>', false); // no WHMCS ID: the store

        Product::onPage('vps')->first()->update(['whmcs_pid' => 120]);
        $this->get('/vps/')->assertSee('href="https://my.hostorio.com/cart.php?a=add&amp;pid=120" class="cloud-order-btn">DEPLOY NOW</a>', false);

        $offer = Product::onPage('offer')->first();
        $this->get('/offer/')->assertSee('pid=16&amp;sld=whmcs&amp;tld=.com', false);
        $offer->update(['whmcs_pid' => 99]);
        $this->get('/offer/')->assertSee('pid=99&amp;sld=whmcs&amp;tld=.com', false);
    }

    public function test_structured_data_is_generated_from_products_and_is_valid_json(): void
    {
        Product::onPage('home')->first()->update(['name' => 'Starter Plus']);

        $schemas = $this->schemas($this->get('/')->getContent(), 'Web Hosting Plan');
        $this->assertCount(4, $schemas);
        $this->assertSame('Starter Plus Web Hosting Plan', $schemas[0]['name']);
        $this->assertSame(3000, $schemas[0]['offers']['price']);

        $offer = $this->schemas($this->get('/offer/')->getContent(), 'AggregateOffer');
        $this->assertSame(999, $offer[0]['lowPrice']);
        $this->assertSame(4500, $offer[0]['highPrice']);
        $this->assertSame(4, $offer[0]['offerCount']);
        $this->assertCount(4, $this->schemas($this->get('/offer/')->getContent(), 'ItemList')[0]['itemListElement']);

        $this->get('/web-hosting/')->assertSee('"lowPrice": 3000,', false)->assertSee('"highPrice": 12000,', false)->assertSee('"offerCount": 4,', false);

        Product::onPage('turbo_hosting')->first()->update(['yearly_price' => 9000]);
        $service = $this->schemas($this->get('/turbo-hosting/')->getContent(), '"Service"')[0]['offers'];
        $this->assertSame([9000, 30000, 4], [$service['lowPrice'], $service['highPrice'], $service['offerCount']]);
    }

    public function test_every_page_has_valid_structured_data(): void
    {
        foreach ([...array_values(self::PAGES), '/about-us/', '/privacy-policy/', '/terms-and-conditions/'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertSame(substr_count($html, 'application/ld+json'), count($this->schemas($html, '@')), $url);
        }
    }

    public function test_every_product_offer_says_when_its_price_starts(): void
    {
        $today = now()->toDateString();
        $offers = 0;

        foreach (self::PAGES as $url) {
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->get($url)->getContent(), $m);

            foreach ($m[1] as $json) {
                $walk = function ($node) use (&$walk, &$offers, $today, $url) {
                    if (! is_array($node)) {
                        return;
                    }
                    if (in_array($node['@type'] ?? null, ['Offer', 'AggregateOffer'], true) && isset($node['priceCurrency'])) {
                        // Google Merchant listings: Missing field "validFrom" (in "offers").
                        $this->assertArrayHasKey('validFrom', $node, "{$url}: " . json_encode($node));
                        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $node['validFrom']);
                        $this->assertLessThanOrEqual($today, $node['validFrom'], $url);
                        $offers++;
                    }
                    foreach ($node as $child) {
                        $walk($child);
                    }
                };
                $walk(json_decode($json, true));
            }
        }

        $this->assertGreaterThan(20, $offers);
    }

    public function test_no_review_markup_without_reviews_shown_on_the_page(): void
    {
        // Google requires marked-up reviews and ratings to be visible on the page.
        // The site shows no star ratings, so the structured data must not claim any.
        foreach ([...array_values(self::PAGES), '/about-us/'] as $url) {
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->get($url)->getContent(), $m);

            foreach ($m[1] as $json) {
                $this->assertDoesNotMatchRegularExpression('/"@type":\s*"(Review|AggregateRating)"|"(review|aggregateRating|reviewRating)":/', $json, $url);
            }
        }
    }

    public function test_admin_text_cannot_break_the_page(): void
    {
        Product::onPage('home')->first()->update(['name' => 'Evil</script><script>alert(1)</script>']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertNotEmpty($this->schemas($html, 'Web Hosting Plan'));
    }

    public function test_package_page_is_gone(): void
    {
        $this->get('/package')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/package');
    }

    public function test_comparison_tables_render_rows_values_and_prices_from_the_admin(): void
    {
        foreach (['web_hosting' => '/web-hosting/', 'turbo_hosting' => '/turbo-hosting/', 'reseller_hosting' => '/reseller-hosting/', 'student_hosting' => '/student-hosting/', 'offer' => '/offer/'] as $page => $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $rows = ComparisonRow::onPage($page)->count();
            $products = Product::onPage($page)->count();

            $this->assertSame($products, substr_count($html, 'class="plan-column'), $page);
            $this->assertSame($rows * $products, substr_count($html, '<div class="plan-feature">'), $page);
            $this->assertSame($rows + 1, substr_count($html, 'class="feature-item-hosting'), $page);
        }

        $web = $this->get('/web-hosting/')->getContent();
        $this->assertStringContainsString('<span class="price-amount">৳3,000/yr</span>', $web);
        $this->assertStringNotContainsString('/mo billed monthly', $web, 'yearly-only plans show no monthly price');
        $this->assertStringContainsString('<div class="plan-feature">5GB NVMe</div>', $web);
        $this->assertStringContainsString('<div class="plan-feature"><i class="fas fa-check"></i></div>', $web);
        $this->assertStringContainsString('<div class="plan-feature"><i class="fas fa-times"></i></div>', $web);

        $this->get('/student-hosting/')->assertSee('<span class="price-amount">৳800/yr</span>', false)->assertSee('<h3 class="plan-title">SH Plan 3 ⭐</h3>', false);
        $this->get('/offer/')->assertSee('<div class="plan-column popular">', false)->assertSee('<span class="price-amount">৳999</span>', false);
    }

    public function test_comparison_tables_follow_admin_edits(): void
    {
        $row = ComparisonRow::onPage('turbo_hosting')->first();
        $row->update(['label' => 'Disk Space']);
        $newRow = ComparisonRow::create(['page_key' => 'turbo_hosting', 'sort_order' => 99, 'label' => 'Malware Scanner']);

        $plus = Product::onPage('turbo_hosting')->first();
        $plus->update([
            'comparison' => array_replace($plus->comparison, [(string) $row->id => ['type' => 'text', 'text' => '80 GB NVMe'], (string) $newRow->id => ['type' => 'yes']]),
            'whmcs_pid' => 77,
        ]);
        Product::onPage('turbo_hosting')->skip(3)->first()->update(['is_visible' => false]);

        $html = $this->get('/turbo-hosting/')->assertOk()->getContent();

        $this->assertStringContainsString('<div class="feature-item-hosting">Disk Space</div>', $html);
        $this->assertStringContainsString('<div class="feature-item-hosting">Malware Scanner</div>', $html);
        $this->assertStringContainsString('<div class="plan-feature">80 GB NVMe</div>', $html);
        $this->assertSame(3, substr_count($html, 'class="plan-column'));
        $this->assertStringContainsString('href="https://my.hostorio.com/cart.php?a=add&amp;pid=77" class="btn-order"', $html);
    }

    public function test_comparison_prices_use_the_live_whmcs_price(): void
    {
        $this->configureWhmcs();
        $this->fakeWhmcs();

        // Web Hosting "Starter" is WHMCS product 55, which returns 3100 BDT a year.
        $this->get('/web-hosting/')->assertSee('<span class="price-amount">৳3,100/yr</span>', false);
    }

    /** @return list<array<string, mixed>> */
    private function schemas(string $html, string $needle): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return collect($m[1])
            ->filter(fn ($json) => str_contains($json, $needle))
            ->map(function ($json) {
                $data = json_decode($json, true);
                $this->assertIsArray($data, 'invalid JSON-LD: ' . substr($json, 0, 120));

                return $data;
            })
            ->values()->all();
    }
}
