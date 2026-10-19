<?php

namespace Tests\Feature;

use App\Filament\Pages\DomainPageSettings;
use App\Filament\Resources\DomainExtensions\Pages\ManageDomainExtensions;
use App\Models\DomainExtension;
use App\Models\SeoPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\DomainPricing;
use App\Services\WhmcsService;
use Database\Seeders\ProductSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class DomainRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    /** WHMCS answering GetTLDPricing in the given currency. */
    private function whmcs(string $currency = 'BDT'): void
    {
        Setting::set('whmcs_url', 'https://my.example.com');
        Setting::set('whmcs_api_identifier', 'abc123');
        WhmcsService::storeSecret('whmcs_api_secret', 's3cret');

        $usd = $currency === 'USD';
        $t = fn ($r, $n, $x, $g = '', $popular = false) => [
            'categories' => $popular ? ['Popular'] : ['Other'], 'group' => $g,
            'register' => ['1' => $r], 'renew' => ['1' => $n], 'transfer' => $x ? ['1' => $x] : [],
        ];

        Http::fake(['my.example.com/*' => Http::response([
            'result' => 'success',
            'currency' => ['code' => $currency, 'prefix' => $usd ? '$' : '৳'],
            'pricing' => [
                'xyz' => $t($usd ? '2.40' : '299.00', $usd ? '11.20' : '1400.00', $usd ? '9.60' : '1200.00', 'sale', true),
                'com.bd' => $t($usd ? '9.20' : '1150.00', $usd ? '9.20' : '1150.00', null),
                'com' => $t($usd ? '11.99' : '1499.00', $usd ? '13.20' : '1650.00', $usd ? '11.99' : '1499.00', 'hot', true),
                'zone' => $t($usd ? '30.00' : '3750.00', $usd ? '30.00' : '3750.00', $usd ? '30.00' : '3750.00'),
            ],
        ])]);
    }

    private function admin(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    public function test_page_shows_live_prices_in_a_sensible_order(): void
    {
        $this->whmcs();

        $html = $this->get('/domain-registration/')->assertOk()->getContent();

        // Preferred order first (.com, .com.bd, .xyz), then the rest.
        $positions = array_map(fn ($tld) => strpos($html, 'data-tld="' . $tld . '"'), ['.com', '.com.bd', '.xyz', '.zone']);
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);

        $this->assertStringContainsString('data-auto-price="11.992">1,499</span>', $html); // BDT shown, USD for the switch
        $this->assertStringContainsString('<span class="dr-badge dr-badge-hot" data-i18n="domainReg.prices.hot">HOT</span>', $html);
        $this->assertStringContainsString('<span class="dr-badge dr-badge-sale"', $html);
        $this->assertStringContainsString('<span class="dr-price dr-price-none">—</span>', $html); // .com.bd has no transfer price
        $this->assertStringContainsString('<strong>.com.bd</strong>', $html); // hero "Popular" line
    }

    public function test_usd_prices_from_whmcs_are_shown_in_taka(): void
    {
        $this->whmcs('USD');

        $row = collect(app(DomainPricing::class)->rows())->firstWhere('tld', '.com');

        $this->assertSame(['bdt' => '৳1,499', 'usd' => 11.99], $row['register']); // 11.99 × 125
    }

    public function test_faq_fills_in_live_prices_and_matches_its_structured_data(): void
    {
        $this->whmcs();

        $html = $this->get('/domain-registration/')->getContent();

        $this->assertStringContainsString('At Hostorio a .com domain costs ৳1,499 for the first year and ৳1,650 a year to renew.', $html);

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $faq = collect($m[1])->map(fn ($json) => json_decode($json, true))->firstWhere('@type', 'FAQPage');
        $this->assertNotNull($faq);
        $this->assertCount(count(DomainPricing::DEFAULT_FAQ), $faq['mainEntity']);

        foreach ($faq['mainEntity'] as $question) {
            $this->assertStringContainsString('<h3 class="faq-question-text">' . e($question['name']) . '</h3>', $html, 'structured data matches the visible questions');
        }
    }

    public function test_admin_controls_order_visibility_and_badges(): void
    {
        $this->whmcs();
        $this->admin();

        Livewire::test(ManageDomainExtensions::class)->assertOk()->assertSee('.zone')->assertSee('৳3,750');
        $this->assertSame(4, DomainExtension::count(), 'opening the list adds every WHMCS extension');

        DomainExtension::where('tld', '.zone')->update(['sort_order' => -1, 'badge' => 'new']);
        DomainExtension::where('tld', '.com')->update(['badge' => 'none']);
        DomainExtension::where('tld', '.xyz')->update(['is_visible' => false]);

        $html = $this->get('/domain-registration/')->getContent();

        $this->assertLessThan(strpos($html, 'data-tld=".com"'), strpos($html, 'data-tld=".zone"'));
        $this->assertStringContainsString('dr-badge-new', $html);
        $this->assertStringNotContainsString('dr-badge-hot', $html);
        $this->assertStringNotContainsString('data-tld=".xyz"', $html);
    }

    public function test_refresh_action_and_faq_editing(): void
    {
        $this->whmcs();
        $this->admin();

        Livewire::test(ManageDomainExtensions::class)->callAction('refresh')->assertNotified('Prices refreshed');

        Livewire::test(DomainPageSettings::class)
            ->fillForm(['faq' => [
                ['question' => 'Is .zone cheap?', 'answer' => 'A .zone domain is {register:.zone}; transfer is {transfer:.nope}.'],
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saved');

        $this->get('/domain-registration/')
            ->assertSee('Is .zone cheap?')
            ->assertSee('A .zone domain is ৳3,750; transfer is see our price list.', false)
            ->assertDontSee('How do I register a .com.bd domain?');

        $this->get('/admin/settings')->assertDontSee('domain_faq');
    }

    public function test_page_still_works_without_whmcs(): void
    {
        $html = $this->get('/domain-registration/')->assertOk()->getContent();

        $this->assertStringContainsString('Live domain prices are being updated', $html);
        $this->assertStringNotContainsString('class="dr-table"', $html);
        $this->assertStringContainsString('see our price list', $html, 'FAQ placeholders never show raw');
    }

    public function test_page_is_linked_and_set_up_for_search_engines(): void
    {
        $home = $this->get('/')->getContent();
        $this->assertStringContainsString('href="' . hostorio_url('domain-registration') . '" class="dropdown-item"', $home);
        $this->assertStringContainsString('href="' . hostorio_url('domain-registration') . '#domain-prices" class="dropdown-item"', $home);
        $this->assertStringContainsString('href="' . hostorio_url('domain-registration') . '" class="service-link"', $home);

        $this->get('/sitemap.xml')->assertSee(hostorio_url('domain-registration'));
        $this->get('/domain-registration')->assertRedirect(hostorio_url('domain-registration'));

        $page = SeoPage::firstWhere('page_key', 'domain_registration');
        $this->assertSame('domain registration', $page->focus_keyword);
        $this->get('/domain-registration/')
            ->assertSee('<title>Domain Registration in Bangladesh - Hostorio</title>', false)
            ->assertSee('<link rel="canonical" href="' . hostorio_url('domain-registration') . '">', false);
    }
}
