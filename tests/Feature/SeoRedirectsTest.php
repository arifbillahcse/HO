<?php

namespace Tests\Feature;

use App\Filament\Pages\LaunchChecklist;
use App\Filament\Pages\SeoSettings;
use App\Filament\Resources\NotFoundLogs\Pages\ManageNotFoundLogs;
use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\NotFoundLog;
use App\Models\Product;
use App\Models\Redirect;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class SeoRedirectsTest extends TestCase
{
    use RefreshDatabase;

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

    private function redirect(string $source, ?string $target, int $status = 301, bool $active = true): Redirect
    {
        return Redirect::create(['source' => Redirect::normalize($source), 'target' => $target, 'status_code' => $status, 'is_active' => $active]);
    }

    public function test_addresses_are_normalised(): void
    {
        $this->assertSame('/old-page', Redirect::normalize('https://hostorio.com/Old-Page/?a=1'));
        $this->assertSame('/old-page', Redirect::normalize('old-page'));
        $this->assertSame('/blog/*', Redirect::normalize('blog/*'));
        $this->assertSame('/', Redirect::normalize(''));
        $this->assertSame('/বাংলা', Redirect::normalize('/%E0%A6%AC%E0%A6%BE%E0%A6%82%E0%A6%B2%E0%A6%BE/'));
    }

    public function test_exact_redirects_ignore_case_and_slash_and_keep_the_query(): void
    {
        $this->redirect('/old-hosting/', '/web-hosting/');

        $this->get('/old-hosting')->assertStatus(301)->assertRedirect(url('/') . '/web-hosting/');
        $this->get('/OLD-Hosting/?utm_source=fb')->assertRedirect(url('/') . '/web-hosting/?utm_source=fb');

        $this->assertSame(2, Redirect::first()->hits);
        $this->assertNotNull(Redirect::first()->last_hit_at);
    }

    public function test_wildcard_redirects_carry_the_rest_of_the_address(): void
    {
        $this->redirect('/blog/*', '/news/*');
        $this->redirect('/blog/old/*', 'https://example.com/archive/');

        $this->get('/blog/my-post/')->assertRedirect(url('/') . '/news/my-post');
        $this->get('/blog')->assertRedirect(url('/') . '/news/');
        $this->get('/blog/old/anything')->assertRedirect('https://example.com/archive/');
    }

    public function test_deleted_inactive_and_existing_page_redirects(): void
    {
        $this->redirect('/removed-page', null, 410);
        $this->redirect('/paused', '/vps/', 301, false);
        $this->redirect('/offer/', '/web-hosting/', 302);

        $this->get('/removed-page/')->assertStatus(410)->assertSee('Page Not Found');
        $this->get('/paused/')->assertNotFound();
        $this->get('/offer/')->assertStatus(302)->assertRedirect(url('/') . '/web-hosting/');
    }

    public function test_admin_and_system_addresses_never_redirect(): void
    {
        Redirect::create(['source' => '/admin', 'target' => '/vps/']);
        Redirect::create(['source' => '/sitemap.xml', 'target' => '/vps/']);
        $this->admin();

        $this->get('/admin')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
    }

    public function test_redirect_admin_validates_and_creates(): void
    {
        $this->admin();

        Livewire::test(ManageRedirects::class)
            ->callAction('create', data: ['source' => 'https://hostorio.com/Old-Page/', 'status_code' => 301, 'target' => '/web-hosting/'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('redirects', ['source' => '/old-page', 'target' => '/web-hosting/', 'status_code' => 301]);
        $this->get('/old-page/')->assertRedirect(url('/') . '/web-hosting/');

        foreach ([
            ['source' => '/', 'target' => '/vps/'],
            ['source' => '/*', 'target' => '/vps/'],
            ['source' => '/OLD-PAGE', 'target' => '/vps/'],
            ['source' => '/admin/users', 'target' => '/vps/'],
            ['source' => '/a-page', 'target' => 'web-hosting'],
            ['source' => '/a-page', 'target' => '/A-Page/'],
        ] as $data) {
            Livewire::test(ManageRedirects::class)
                ->callAction('create', data: $data + ['status_code' => 301])
                ->assertHasFormErrors();
        }

        Livewire::test(ManageRedirects::class)
            ->callAction('create', data: ['source' => '/gone-page', 'status_code' => 410])
            ->assertHasNoFormErrors();
        $this->get('/gone-page/')->assertStatus(410);
    }

    public function test_missing_addresses_are_logged(): void
    {
        $this->get('/wp-content/uploads/2026/05/3-1024x704.jpg', ['Referer' => 'https://facebook.com/', 'User-Agent' => 'facebookexternalhit/1.1'])->assertNotFound();
        $this->get('/wp-content/uploads/2026/05/3-1024x704.jpg')->assertNotFound();
        $this->get('/category/news/')->assertNotFound();
        $this->get('/admin/nothing-here')->assertNotFound();
        $this->get('/web-hosting/')->assertOk();

        $log = NotFoundLog::firstWhere('path', '/wp-content/uploads/2026/05/3-1024x704.jpg');
        $this->assertSame(2, $log->hits);
        $this->assertSame(2, NotFoundLog::count(), 'admin addresses and working pages are not logged');
        $this->assertSame('https://facebook.com/', $log->referrer, 'a later visit without a referrer keeps the last one');
    }

    public function test_the_log_cannot_grow_forever(): void
    {
        $rows = collect(range(1, NotFoundLog::MAX_ROWS))->map(fn ($i) => [
            'path' => "/probe-{$i}", 'hits' => 1, 'last_hit_at' => now()->subDays(10)->addSeconds($i), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $rows->chunk(500)->each(fn ($chunk) => DB::table('not_found_logs')->insert($chunk->all()));

        $this->get('/brand-new-404/')->assertNotFound();

        $this->assertSame(NotFoundLog::MAX_ROWS, NotFoundLog::count());
        $this->assertDatabaseHas('not_found_logs', ['path' => '/brand-new-404/']);
        $this->assertDatabaseMissing('not_found_logs', ['path' => '/probe-1']);
    }

    public function test_404_monitor_turns_entries_into_redirects(): void
    {
        $this->admin();
        foreach (['/old-a/', '/old-b', '/old-c'] as $path) {
            NotFoundLog::record($path, null, null);
        }
        [$a, $b, $c] = NotFoundLog::orderBy('path')->get()->all();

        Livewire::test(ManageNotFoundLogs::class)
            ->callAction(TestAction::make('redirect')->table($a), data: ['status_code' => 301, 'target' => '/'])
            ->assertHasNoFormErrors()
            ->assertNotified('Redirect created');

        $this->assertDatabaseHas('redirects', ['source' => '/old-a', 'target' => '/']);
        $this->assertModelMissing($a);
        $this->get('/old-a/')->assertRedirect(url('/') . '/');

        Livewire::test(ManageNotFoundLogs::class)
            ->selectTableRecords([$b->getKey(), $c->getKey()])
            ->callAction(TestAction::make('redirect')->table()->bulk(), data: ['status_code' => 410])
            ->assertNotified('2 redirects created');

        $this->get('/old-b/')->assertStatus(410);
        $this->assertSame(0, NotFoundLog::count());

        NotFoundLog::record('/again', null, null);
        Livewire::test(ManageNotFoundLogs::class)->callAction('clear')->assertNotified('404 log cleared');
        $this->assertSame(0, NotFoundLog::count());
    }

    public function test_robots_txt_default_and_custom(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin/')
            ->assertSee('Sitemap: ' . url('sitemap.xml'))
            ->assertDontSee('Disallow: /storage/');

        $this->admin();
        Livewire::test(SeoSettings::class)
            ->fillForm(['seo_robots_txt' => "User-agent: *\nDisallow: /"])
            ->call('save')
            ->assertHasFormErrors(['seo_robots_txt']);

        Livewire::test(SeoSettings::class)
            ->fillForm(['seo_robots_txt' => "User-agent: *\nDisallow: /private/\n\nSitemap: https://hostorio.com/sitemap.xml"])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/robots.txt')->assertSee('Disallow: /private/')->assertDontSee('Disallow: /admin/');
    }

    public function test_old_rank_math_sitemaps_redirect_and_dates_follow_edits(): void
    {
        foreach (['sitemap_index.xml', 'page-sitemap.xml', 'post-sitemap.xml', 'local-sitemap.xml'] as $old) {
            $this->get("/{$old}")->assertStatus(301)->assertRedirect(url('sitemap.xml'));
        }

        $changed = now()->addYear()->startOfSecond();
        Product::query()->where('page_key', 'vps')->first()->forceFill(['updated_at' => $changed])->saveQuietly();

        $sitemap = $this->get('/sitemap.xml')->getContent();
        $this->assertStringContainsString('<loc>' . hostorio_url('vps') . "</loc>\n    <lastmod>" . $changed->toAtomString() . '</lastmod>', $sitemap);
        $this->assertSame(1, substr_count($sitemap, $changed->toAtomString()), 'only the VPS page changed');
    }

    public function test_site_keeps_working_if_the_new_tables_are_missing(): void
    {
        Schema::drop('redirects');
        Schema::drop('not_found_logs');
        Cache::flush();

        $this->get('/')->assertOk();
        $this->get('/web-hosting/')->assertOk();
        $this->get('/no-such-page/')->assertNotFound();
    }

    public function test_launch_checklist_reports_the_state(): void
    {
        $this->admin();

        $this->get('/admin/launch-checklist')
            ->assertOk()
            ->assertSee('Search engines can index the site')
            ->assertSee('Page addresses keep their trailing slash')
            ->assertSee('1 of 11 pages have a meta description')
            ->assertSee('Sitemap lists 11 pages')
            ->assertSee('On launch day');

        $checks = collect((new LaunchChecklist())->checks())->keyBy('title');
        $this->assertSame('warn', $checks['No default share image']['status']);
        $this->assertSame('ok', $checks['Analytics is on']['status']);
    }
}
