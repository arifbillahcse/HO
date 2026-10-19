<?php

namespace App\Filament\Pages;

use App\Filament\Resources\NotFoundLogs\NotFoundLogResource;
use App\Filament\Resources\SeoPages\SeoPageResource;
use App\Models\NotFoundLog;
use App\Models\SeoPage;
use App\Services\Seo;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Automatic checks and manual steps for moving hostorio.com from WordPress to this site.
 */
class LaunchChecklist extends Page
{
    protected static ?string $title = 'Launch checklist';
    protected static ?string $navigationLabel = 'Launch checklist';
    protected static string | UnitEnum | null $navigationGroup = 'SEO';
    protected static ?int $navigationSort = 6;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    public function getSubheading(): ?string
    {
        return 'Checks this installation is ready to replace the WordPress site on hostorio.com without losing rankings.';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.seo.launch-checklist')->viewData(fn (): array => [
                'checks' => $this->checks(),
                'steps' => $this->steps(),
            ]),
        ]);
    }

    /** @return list<array{status: string, title: string, detail: string, link?: string}> */
    public function checks(): array
    {
        $seo = app(Seo::class);
        $settingsUrl = SeoSettings::getUrl();
        $checks = [];

        $checks[] = $seo->isHidden()
            ? ['status' => 'fail', 'title' => 'Hidden from search engines', 'detail' => 'Right for the demo site. On hostorio.com, turn off "Hide this site from search engines".', 'link' => $settingsUrl]
            : ['status' => 'ok', 'title' => 'Search engines can index the site', 'detail' => 'Pages say "index, follow".'];

        $checks[] = config('app.debug')
            ? ['status' => 'fail', 'title' => 'Debug mode is on', 'detail' => 'Set APP_DEBUG=false in .env, then run php artisan optimize:clear. Error pages would show your code to visitors.']
            : ['status' => 'ok', 'title' => 'Debug mode is off', 'detail' => 'Visitors never see error details.'];

        $appUrl = rtrim((string) config('app.url'), '/');
        $here = request()->getSchemeAndHttpHost();
        $checks[] = match (true) {
            ! str_starts_with($appUrl, 'https://') => ['status' => 'fail', 'title' => 'Site address is not https', 'detail' => "APP_URL is {$appUrl}. Set it to the https address in .env."],
            parse_url($appUrl, PHP_URL_HOST) !== request()->getHost() => ['status' => 'warn', 'title' => 'Site address does not match this domain', 'detail' => "APP_URL is {$appUrl} but you are on {$here}. Update APP_URL in .env, then run php artisan optimize:clear."],
            default => ['status' => 'ok', 'title' => 'Site address is correct', 'detail' => $appUrl],
        };

        $checks[] = file_exists(public_path('storage'))
            ? ['status' => 'ok', 'title' => 'Uploaded images are reachable', 'detail' => 'The storage link exists.']
            : ['status' => 'fail', 'title' => 'Uploaded images will not load', 'detail' => 'Run php artisan storage:link.'];

        $slashRule = collect([base_path('.htaccess'), public_path('.htaccess')])
            ->filter(fn (string $file) => is_file($file) && preg_match('/^\s*RewriteCond\s+%\{REQUEST_URI\}\s+\(\.\+\)\/\$/mi', (string) file_get_contents($file)))
            ->map(fn (string $file) => str_replace(base_path() . '/', '', $file));
        $checks[] = $slashRule->isNotEmpty()
            ? ['status' => 'fail', 'title' => 'A server rule removes trailing slashes', 'detail' => 'Delete the "Redirect Trailing Slashes" lines from ' . $slashRule->implode(', ') . ', or pages will redirect in a loop.']
            : ['status' => 'ok', 'title' => 'Page addresses keep their trailing slash', 'detail' => 'Same addresses as WordPress, e.g. ' . Seo::url('web-hosting')];

        $robots = $seo->robotsTxt();
        $checks[] = match (true) {
            (bool) preg_match('/^\s*Disallow:\s*\/\s*$/mi', $robots) => ['status' => 'fail', 'title' => 'robots.txt blocks the whole site', 'detail' => 'Remove "Disallow: /" in SEO Settings → robots.txt.', 'link' => $settingsUrl],
            ! str_contains(strtolower($robots), 'sitemap:') => ['status' => 'warn', 'title' => 'robots.txt has no Sitemap line', 'detail' => 'Add: Sitemap: ' . url('sitemap.xml'), 'link' => $settingsUrl],
            default => ['status' => 'ok', 'title' => 'robots.txt allows search engines', 'detail' => url('robots.txt')],
        };

        $pages = SeoPage::query()->orderBy('sort_order')->get();
        $indexed = $pages->where('robots_index', true)->count();
        $checks[] = ['status' => $indexed > 0 ? 'ok' : 'fail', 'title' => "Sitemap lists {$indexed} pages", 'detail' => url('sitemap.xml') . '. Old Rank Math sitemap addresses redirect to it.'];

        $missing = $pages->filter(fn (SeoPage $p) => blank($p->meta_description))->pluck('name');
        $checks[] = $missing->isEmpty()
            ? ['status' => 'ok', 'title' => 'Every page has a meta description', 'detail' => $pages->count() . ' of ' . $pages->count() . ' pages.']
            : ['status' => 'warn', 'title' => ($pages->count() - $missing->count()) . ' of ' . $pages->count() . ' pages have a meta description', 'detail' => 'Missing: ' . $missing->implode(', ') . '. Google writes its own snippet until you add one.', 'link' => SeoPageResource::getUrl()];

        $checks[] = filled(SettingsService::get('seo_default_image'))
            ? ['status' => 'ok', 'title' => 'Default share image is set', 'detail' => 'Used when pages are shared on social media.']
            : ['status' => 'warn', 'title' => 'No default share image', 'detail' => 'Shared links show no picture. Upload one (1200 × 630) in SEO Settings.', 'link' => $settingsUrl];

        $checks[] = $seo->faviconUrl()
            ? ['status' => 'ok', 'title' => 'Site icon is set', 'detail' => 'Shown in browser tabs and Google results.']
            : ['status' => 'warn', 'title' => 'No site icon (favicon)', 'detail' => 'Upload a square PNG in SEO Settings.', 'link' => $settingsUrl];

        $analytics = filled(SettingsService::get('seo_ga4_id')) || filled(SettingsService::get('seo_gtm_id'));
        $checks[] = match (true) {
            ! $analytics => ['status' => 'warn', 'title' => 'No analytics', 'detail' => 'Add the Google Analytics ID (G-PJSW407GJS) in SEO Settings, or visits are not counted.', 'link' => $settingsUrl],
            $seo->isHidden() => ['status' => 'info', 'title' => 'Analytics paused', 'detail' => 'Analytics is set but switched off while the site is hidden from search engines.'],
            default => ['status' => 'ok', 'title' => 'Analytics is on', 'detail' => 'Google Analytics / Tag Manager code is on every page.'],
        };

        $checks[] = filled(SettingsService::get('seo_verify_google'))
            ? ['status' => 'ok', 'title' => 'Google Search Console code is set', 'detail' => 'The verification tag is on every page.']
            : ['status' => 'info', 'title' => 'No Google Search Console code', 'detail' => 'Fine if hostorio.com is verified with a DNS record. Otherwise add the code in SEO Settings.', 'link' => $settingsUrl];

        $business = hostorio_business();
        $checks[] = ['status' => 'info', 'title' => 'Contact details match Google Maps?', 'detail' => "The site shows {$business->phone()}, {$business->street()}, {$business->cityLine()}. Use the same phone and address as your Google Business Profile.", 'link' => ContactAddress::getUrl()];

        $notFound = NotFoundLog::query()->count();
        $checks[] = ['status' => 'info', 'title' => "{$notFound} addresses in the 404 Monitor", 'detail' => 'After the switch, check it every few days for two weeks and redirect old addresses that matter.', 'link' => NotFoundLogResource::getUrl()];

        return $checks;
    }

    /** @return list<string> */
    public function steps(): array
    {
        return [
            'Back up the WordPress site: files and database (cPanel → Backup).',
            'Point hostorio.com at this site: in cPanel, set the domain\'s document root to the Laravel "public" folder. Set APP_URL=https://hostorio.com in .env and run php artisan optimize:clear.',
            'Open this checklist on hostorio.com/admin. Everything above should be green, apart from the blue notes, and "Hide this site from search engines" must be off.',
            'Open a few pages and compare titles with the old site (right-click → View page source). Open https://hostorio.com/web-hosting (no slash): it should land on /web-hosting/.',
            'Purge the Cloudflare cache (Caching → Purge Everything).',
            'Google Search Console: Sitemaps → submit https://hostorio.com/sitemap.xml and remove sitemap_index.xml. Then URL Inspection → homepage → Request indexing.',
            'Google Analytics → Reports → Realtime: open the site and check your own visit appears.',
            'Facebook Sharing Debugger (developers.facebook.com/tools/debug): enter https://hostorio.com/ and click "Scrape Again" so shares use the new image.',
            'For the next two weeks, check the 404 Monitor every few days and redirect anything that used to exist.',
            'Keep the demo site hidden from search engines.',
        ];
    }
}
