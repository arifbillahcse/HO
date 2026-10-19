<?php

namespace Tests\Feature;

use App\Filament\Pages\LocalSeoSettings;
use App\Filament\Resources\SeoPages\Pages\EditSeoPage;
use App\Models\SeoPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\SeoAnalyzer;
use Database\Seeders\ProductSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SeoScoreAndLocalSeoTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        '/', '/web-hosting/', '/turbo-hosting/', '/reseller-hosting/', '/student-hosting/', '/vps/',
        '/business-email/', '/offer/', '/about-us/', '/privacy-policy/', '/terms-and-conditions/',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    private function admin(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    /** @return list<array<string, mixed>> */
    private function schemas(string $url): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->get($url)->assertOk()->getContent(), $m);

        return array_map(function (string $json) use ($url) {
            $data = json_decode($json, true);
            $this->assertIsArray($data, "{$url}: invalid JSON-LD");

            return $data;
        }, $m[1]);
    }

    private function schema(string $url, string $type): ?array
    {
        return collect($this->schemas($url))->firstWhere('@type', $type);
    }

    /* ───────────── Local SEO ───────────── */

    public function test_business_details_default_to_the_current_site(): void
    {
        $organization = $this->schema('/', 'Organization');
        $this->assertSame('Hostorio', $organization['name']);
        $this->assertSame(asset('assets/images/logo/hostorio-logo.png'), $organization['logo']);
        $this->assertStringStartsWith('Hostorio is a web hosting services provider in Bangladesh', $organization['description']);
        $this->assertSame(['English', 'Bengali'], $organization['contactPoint']['availableLanguage']);
        $this->assertSame('+8801779440297', $organization['contactPoint']['telephone']);

        $local = $this->schema('/', 'LocalBusiness');
        $this->assertSame('৳৳', $local['priceRange']);
        $this->assertSame(['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], 'opens' => '00:00', 'closes' => '23:59'], $local['openingHoursSpecification'][0]);

        // Every page with business data now carries the same, complete block.
        foreach (['/web-hosting/', '/turbo-hosting/', '/vps/', '/business-email/', '/offer/'] as $url) {
            $this->assertSame($organization, $this->schema($url, 'Organization'), $url);
        }
        $this->assertSame('Hostorio', $this->schema('/about-us/', 'AboutPage')['mainEntity']['name']);
    }

    public function test_local_seo_page_changes_what_google_sees(): void
    {
        $this->admin();
        $this->get('/admin/local-seo')->assertOk()->assertSee('Business type');

        Livewire::test(LocalSeoSettings::class)
            ->fillForm([
                'biz_type' => 'ProfessionalService',
                'biz_description' => 'Fast hosting from Narayanganj.',
                'biz_price_range' => '৳',
                'biz_area_served' => 'Bangladesh',
                'biz_languages' => ['Bengali'],
                'biz_hours_247' => false,
                'biz_hours' => [
                    ['days' => ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'], 'opens' => '09:00', 'closes' => '21:00'],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saved');

        $local = $this->schema('/web-hosting/', 'ProfessionalService');
        $this->assertNotNull($local, 'business type is used');
        $this->assertSame('৳', $local['priceRange']);
        $this->assertSame('Bangladesh', $local['areaServed']);
        $this->assertSame(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Saturday', 'Sunday'], $local['openingHoursSpecification'][0]['dayOfWeek']);
        $this->assertSame(['09:00', '21:00'], [$local['openingHoursSpecification'][0]['opens'], $local['openingHoursSpecification'][0]['closes']]);

        $about = $this->schema('/about-us/', 'AboutPage');
        $this->assertSame('Fast hosting from Narayanganj.', $about['description']);
        $this->assertSame(['Bengali'], $about['mainEntity']['contactPoint']['availableLanguage']);
    }

    public function test_online_business_has_no_local_business_block(): void
    {
        Setting::set('biz_type', 'OnlineBusiness');

        $this->assertNull($this->schema('/', 'LocalBusiness'));
        $this->assertNotNull($this->schema('/', 'OnlineBusiness'));
    }

    public function test_uploaded_logo_is_used(): void
    {
        Setting::set('biz_logo', 'seo/logo.png');

        $this->assertSame(asset('storage/seo/logo.png'), $this->schema('/', 'Organization')['logo']);
        $this->assertSame(asset('storage/seo/logo.png'), $this->schema('/', 'LocalBusiness')['image']);
    }

    public function test_business_keys_are_hidden_from_generic_settings(): void
    {
        $this->admin();
        Setting::set('biz_price_range', '৳৳৳-hidden');

        $this->get('/admin/settings')->assertOk()->assertDontSee('biz_price_range')->assertDontSee('৳৳৳-hidden');
    }

    /* ───────────── SEO score ───────────── */

    public function test_page_text_is_read_from_the_main_content(): void
    {
        $content = app(SeoAnalyzer::class)->extract(<<<'HTML'
            <html><body><header><a href="/web-hosting/">Menu</a><h2>Menu heading</h2></header>
            <main>
              <h1>Best Web Hosting</h1>
              <h2>Why web hosting matters</h2>
              <p>Our web hosting is fast. <a href="/vps/">VPS</a> <a href="https://example.com/">Elsewhere</a> <a href="#top">Top</a></p>
              <img src="a.png" alt="web hosting server"><img src="b.png"><img src="c.png" alt="">
              <script>var hidden = "web hosting";</script>
            </main><footer>Footer words</footer></body></html>
            HTML);

        $this->assertSame(['Best Web Hosting'], $content['h1']);
        $this->assertSame(['Why web hosting matters'], $content['subheadings']);
        $this->assertSame(['web hosting server', null, ''], $content['images']);
        $this->assertSame(1, $content['internal_links'], 'only links in the main content, not #anchors or other sites');
        $this->assertStringNotContainsString('hidden', $content['text']);
        $this->assertStringNotContainsString('Footer', $content['text']);
        $this->assertSame(15, $content['words']); // 3 + 4 + 5 + VPS, Elsewhere, Top
    }

    public function test_score_checks_the_focus_keyword_and_basics(): void
    {
        $analyzer = app(SeoAnalyzer::class);
        $content = $analyzer->content(SeoPage::firstWhere('page_key', 'web_hosting'));

        $good = $analyzer->analyze([
            'title' => 'Web Hosting Bangladesh - Fast NVMe Hosting | Hostorio',
            'description' => 'Affordable web hosting in Bangladesh with free SSL, NVMe storage, LiteSpeed and 24/7 support. Plans from 999 taka a year, free domain.',
            'keyword' => 'web hosting',
            'indexed' => true,
        ], $content);
        $this->assertSame(100, $good['score']);

        $none = $analyzer->analyze(['title' => 'Web Hosting', 'description' => '', 'keyword' => '', 'indexed' => true], $content);
        $this->assertLessThan(30, $none['score']);
        $this->assertContains('Add a focus keyword', array_column($none['checks'], 'label'));
        $this->assertContains('No meta description', array_column($none['checks'], 'label'));

        // Whole words only: "host" is not found inside "hosting".
        $partial = $analyzer->analyze(['title' => 'Hosting plans', 'description' => '', 'keyword' => 'host', 'indexed' => true], $content);
        $this->assertContains('Add the focus keyword to the SEO title', array_column($partial['checks'], 'label'));

        $hidden = $analyzer->analyze(['title' => 'x', 'description' => '', 'keyword' => '', 'indexed' => false], $content);
        $this->assertSame('This page is set to noindex', $hidden['checks'][0]['label']);
    }

    public function test_admin_shows_scores_and_updates_them_live(): void
    {
        $this->admin();

        $this->get('/admin/seo-pages')->assertOk()->assertSee('SEO score')->assertSee('/ 100');

        $page = SeoPage::firstWhere('page_key', 'web_hosting');
        Livewire::test(EditSeoPage::class, ['record' => $page->getRouteKey()])
            ->assertSee('Add a focus keyword')
            ->fillForm(['focus_keyword' => 'web hosting'])
            ->assertSee('Focus keyword is in the SEO title')
            ->assertSee('Keyword density')
            ->fillForm(['meta_description' => 'Affordable web hosting in Bangladesh with free SSL, NVMe storage, LiteSpeed and 24/7 support. Plans from 999 taka a year, free domain.'])
            ->assertSee('Focus keyword is in the meta description');
    }

    public function test_pages_have_no_empty_links(): void
    {
        foreach (self::PAGES as $url) {
            $this->assertStringNotContainsString('href=""', $this->get($url)->assertOk()->getContent(), $url);
        }

        $this->get('/')->assertSee('href="' . hostorio_url('business-email') . '" class="service-link"', false);
    }
}
