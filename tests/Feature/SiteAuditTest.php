<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Database\Seeders\ProductSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteAuditTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        '/', '/web-hosting/', '/turbo-hosting/', '/reseller-hosting/', '/student-hosting/', '/vps/',
        '/business-email/', '/offer/', '/domain-registration/', '/about-us/', '/privacy-policy/', '/terms-and-conditions/',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_no_button_or_link_goes_nowhere(): void
    {
        foreach (self::PAGES as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            // Only menu toggles may use a bare "#".
            preg_match_all('#<a href="\#"([^>]*)>#', $html, $m);
            foreach ($m[1] as $attributes) {
                $this->assertMatchesRegularExpression('/nav-item|dropdown/', $attributes, "{$url}: dead link {$attributes}");
            }

            // In-page links must point at an element that exists.
            preg_match_all('#href="\#([A-Za-z][\w-]*)"#', $html, $anchors);
            foreach (array_unique($anchors[1]) as $id) {
                $this->assertStringContainsString('id="' . $id . '"', $html, "{$url}: #{$id} has no target");
            }
        }
    }

    public function test_plans_without_a_whmcs_id_open_the_store(): void
    {
        $this->get('/turbo-hosting/')->assertSee('href="https://my.hostorio.com/cart.php" class="cloud-order-btn"', false);
        $this->get('/business-email/')
            ->assertSee('href="https://my.hostorio.com/submitticket.php" class="faq-cta-button"', false)
            ->assertSee('href="https://my.hostorio.com/knowledgebase" class="jellyfish-link"', false);
        $this->get('/offer/')->assertSee('<a href="#offer-plans" class="countdown-cta">', false);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
        $this->get('/admin/launch-checklist')->assertSee('plans have no WHMCS product ID')->assertSee('Turbo Hosting (');
    }

    public function test_home_plans_are_sold_yearly_only(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="billingToggle"', false)->assertDontSee('Billed Monthly');
        Product::onPage('home')->first()->update(['monthly_price' => 350]);
        $this->get('/')->assertDontSee('id="billingToggle"', false); // even when the plan has a monthly price
    }

    public function test_testimonial_read_more_follows_the_reviews_link(): void
    {
        $this->get('/web-hosting/')->assertDontSee('testimonial-read-more', false);

        Setting::set('reviews_url', 'https://g.page/r/hostorio/review');

        $this->get('/web-hosting/')->assertSee('<a href="https://g.page/r/hostorio/review" class="testimonial-read-more" target="_blank" rel="noopener"', false);
    }

    public function test_favicon_ico_serves_the_uploaded_icon(): void
    {
        $this->get('/favicon.ico')->assertNoContent();

        Setting::set('seo_favicon', 'seo/icon.png');

        $this->get('/favicon.ico')->assertRedirect(asset('storage/seo/icon.png'));
    }

    public function test_uploaded_files_use_relative_urls_so_previews_work_on_any_domain(): void
    {
        $this->assertSame('/storage/seo/icon.png', Storage::disk('public')->url('seo/icon.png'));
    }

    public function test_error_pages_work_without_the_database(): void
    {
        DB::enableQueryLog();

        foreach (['errors.500' => 'Something went wrong', 'errors.503' => 'Back in a moment'] as $view => $text) {
            $html = view($view)->render();
            $this->assertStringContainsString($text, $html);
            $this->assertStringContainsString('noindex', $html);
        }

        $this->assertSame([], DB::getQueryLog());
    }

    public function test_production_links_stay_on_https_behind_a_proxy(): void
    {
        config(['app.url' => 'https://hostorio.com']);
        $this->app['env'] = 'production';

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/web-hosting'));
        $this->assertStringStartsWith('https://', hostorio_url('vps'));
    }
}
