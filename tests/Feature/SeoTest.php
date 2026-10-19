<?php

namespace Tests\Feature;

use App\Filament\Pages\SeoSettings;
use App\Filament\Resources\SeoPages\Pages\EditSeoPage;
use App\Models\SeoPage;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SeoTest extends TestCase
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

    private function admin(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    public function test_every_page_names_itself_as_canonical(): void
    {
        foreach (self::PAGES as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('#<link rel="canonical" href="([^"]+)">#', $html, $path);
            preg_match('#<link rel="canonical" href="([^"]+)">#', $html, $m);
            $this->assertSame(rtrim(url($path), '/') . '/', $m[1], "{$path}: canonical must point to the page itself");
            $this->assertSame(1, substr_count($html, '<title>'), "{$path}: exactly one title");
        }
    }

    public function test_pages_match_the_rank_math_output(): void
    {
        $this->get('/')
            ->assertSee('<title>Home - Fast Web Hosting ৳999/yr | Free SSL &amp; Domain | Hostorio</title>', false)
            ->assertSee('<meta name="robots" content="follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large">', false)
            ->assertSee('<meta property="og:site_name" content="Fast Web Hosting ৳999/yr | Free SSL &amp; Domain | Hostorio">', false)
            ->assertSee('<meta property="og:locale" content="en_US">', false)
            ->assertDontSee('<meta name="description"', false)
            ->assertSee('gtag/js?id=G-PJSW407GJS', false)
            ->assertSee('<script src="https://chat.hostorio.com/widget/widget.js"', false);

        $this->get('/offer/')->assertSee('<title>Offer - Fast Web Hosting ৳999/yr | Free SSL &amp; Domain | Hostorio</title>', false);
        $this->get('/terms-and-conditions/')->assertSee('<meta name="description" content="Read the Terms and Conditions', false);
    }

    public function test_addresses_get_the_wordpress_trailing_slash(): void
    {
        $this->get('/web-hosting')->assertStatus(301)->assertRedirect(hostorio_url('web-hosting'));
        $this->get('/vps?ref=ad')->assertStatus(301)->assertRedirect(hostorio_url('vps') . '?ref=ad');
        $this->get('/home/')->assertStatus(301)->assertRedirect(url('/') . '/');
        $this->get('/no-such-page/')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk();

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('href="' . hostorio_url('web-hosting') . '"', $html, 'menu links use the slash address');
        $this->assertStringNotContainsString('href="' . url('/web-hosting') . '"', $html);

        $sitemap = $this->get('/sitemap.xml')->getContent();
        $this->assertStringContainsString('<loc>' . hostorio_url('web-hosting') . '</loc>', $sitemap);
        $this->assertSame(12, substr_count($sitemap, '<loc>'));
    }

    public function test_page_seo_edits_show_on_the_website(): void
    {
        $this->admin();
        $page = SeoPage::firstWhere('page_key', 'web_hosting');

        Livewire::test(EditSeoPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'seo_title' => 'Cheap %title% in Bangladesh %sep% Hostorio',
                'meta_description' => 'Fast NVMe web hosting with free SSL.',
                'focus_keyword' => 'web hosting bangladesh',
                'og_title' => 'Share title',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/web-hosting/')
            ->assertSee('<title>Cheap Web Hosting in Bangladesh - Hostorio</title>', false)
            ->assertSee('<meta name="description" content="Fast NVMe web hosting with free SSL.">', false)
            ->assertSee('<meta property="og:title" content="Share title">', false)
            ->assertSee('<meta property="og:description" content="Fast NVMe web hosting with free SSL.">', false);

        $this->get('/admin/seo-pages')->assertOk()->assertSee('Cheap Web Hosting in Bangladesh - Hostorio')->assertSee('web hosting bangladesh');
    }

    public function test_noindex_page_leaves_search_and_the_sitemap(): void
    {
        $this->admin();
        $page = SeoPage::firstWhere('page_key', 'privacy_policy');

        Livewire::test(EditSeoPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['robots_index' => false, 'robots_follow' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/privacy-policy/')->assertSee('<meta name="robots" content="nofollow, noindex">', false);
        $this->get('/sitemap.xml')->assertDontSee('privacy-policy');
    }

    public function test_share_images_with_page_image_before_default_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('seo/default.jpg', UploadedFile::fake()->image('default.jpg', 1200, 630)->getContent());
        Storage::disk('public')->put('seo/vps.png', UploadedFile::fake()->image('vps.png', 800, 400)->getContent());
        Setting::set('seo_default_image', 'seo/default.jpg');
        SeoPage::firstWhere('page_key', 'vps')->update(['og_image' => 'seo/vps.png']);

        $this->get('/')
            ->assertSee('<meta property="og:image" content="' . asset('storage/seo/default.jpg') . '">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:type" content="image/jpeg">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);

        $this->get('/vps/')
            ->assertSee('<meta property="og:image" content="' . asset('storage/seo/vps.png') . '">', false)
            ->assertSee('<meta property="og:image:height" content="400">', false);
    }

    public function test_seo_settings_page_saves_site_wide_options(): void
    {
        $this->admin();

        $this->get('/admin/seo-settings')->assertOk()->assertSee('G-PJSW407GJS');

        Livewire::test(SeoSettings::class)
            ->fillForm([
                'seo_site_name' => 'Hostorio',
                'seo_separator' => '|',
                'seo_title_pattern' => '%title% %sep% %sitename%',
                'seo_verify_google' => '<meta name="google-site-verification" content="abc123XYZ" />',
                'seo_verify_bing' => 'BING42',
                'seo_ga4_id' => 'g-new1234',
                'seo_gtm_id' => 'gtm-abc12',
                'seo_head_code' => '<meta name="custom-head" content="1">',
                'seo_footer_code' => '<script>window.footerCode = 1;</script>',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saved');

        $html = $this->get('/web-hosting/')->getContent();
        $this->assertStringContainsString('<title>Web Hosting | Hostorio</title>', $html);
        $this->assertStringContainsString('<meta name="google-site-verification" content="abc123XYZ">', $html);
        $this->assertStringContainsString('<meta name="msvalidate.01" content="BING42">', $html);
        $this->assertStringContainsString('gtag/js?id=G-NEW1234', $html);
        $this->assertStringContainsString("'GTM-ABC12'", $html);
        $this->assertStringContainsString('ns.html?id=GTM-ABC12', $html);
        $this->assertStringContainsString('<meta name="custom-head" content="1">', $html);
        $this->assertStringContainsString('<script>window.footerCode = 1;</script>', $html);
        $this->assertStringNotContainsString('chat.hostorio.com', $html, 'footer code replaced');
    }

    public function test_invalid_analytics_ids_are_rejected(): void
    {
        $this->admin();

        Livewire::test(SeoSettings::class)
            ->fillForm(['seo_ga4_id' => 'UA-12345-1', 'seo_gtm_id' => 'G-123'])
            ->call('save')
            ->assertHasFormErrors(['seo_ga4_id', 'seo_gtm_id']);
    }

    public function test_hiding_the_site_noindexes_everything_and_stops_analytics(): void
    {
        $this->admin();

        Livewire::test(SeoSettings::class)
            ->fillForm(['seo_hide_site' => true])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saved');

        auth()->logout();

        foreach (['/', '/web-hosting/'] as $path) {
            $this->get($path)
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
                ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
                ->assertDontSee('googletagmanager.com', false)
                ->assertSee('chat.hostorio.com', false);
        }

        $this->get('/sitemap.xml')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/no-such-page/')->assertNotFound()->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_not_found_page_does_not_borrow_page_tags(): void
    {
        $html = $this->get('/no-such-page/')->assertNotFound()->getContent();

        $this->assertStringNotContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('<title>Page Not Found – Hostorio</title>', $html);
        $this->assertStringContainsString('noindex', $html);
    }

    public function test_admin_text_is_escaped(): void
    {
        SeoPage::firstWhere('page_key', 'home')->update([
            'seo_title' => '</title><script>alert(1)</script>',
            'meta_description' => '"><script>alert(2)</script>',
        ]);

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<script>alert(2)</script>', $html);
    }

    public function test_seo_keys_are_hidden_from_generic_settings(): void
    {
        $this->admin();
        Setting::set('seo_ga4_id', 'G-HIDDEN1');

        $this->get('/admin/settings')->assertOk()->assertDontSee('seo_ga4_id')->assertDontSee('G-HIDDEN1');
    }
}
