<?php

namespace App\Services;

use App\Models\SeoPage;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Titles, meta tags, verification codes and tracking code, edited under Admin → SEO.
 */
class Seo
{
    public const DEFAULT_SITE_NAME = 'Fast Web Hosting ৳999/yr | Free SSL & Domain | Hostorio';
    public const DEFAULT_PATTERN = '%title% %sep% %sitename%';
    public const SEPARATORS = ['-' => '-', '–' => '–', '|' => '|', '·' => '·', '»' => '»', '/' => '/'];

    /** Rank Math's default extra directives for indexable pages. */
    public const ROBOTS_EXTRA = 'max-snippet:-1, max-video-preview:-1, max-image-preview:large';

    /** Setting key => meta name. */
    public const VERIFICATION = [
        'seo_verify_google' => 'google-site-verification',
        'seo_verify_bing' => 'msvalidate.01',
        'seo_verify_yandex' => 'yandex-verification',
        'seo_verify_baidu' => 'baidu-site-verification',
        'seo_verify_pinterest' => 'p:domain_verify',
        'seo_verify_facebook' => 'facebook-domain-verification',
    ];

    /** Setting keys managed by the SEO Settings page. */
    public const KEYS = [
        'seo_site_name', 'seo_separator', 'seo_title_pattern', 'seo_hide_site',
        'seo_default_image', 'seo_favicon',
        'seo_verify_google', 'seo_verify_bing', 'seo_verify_yandex', 'seo_verify_baidu', 'seo_verify_pinterest', 'seo_verify_facebook',
        'seo_ga4_id', 'seo_gtm_id', 'seo_head_code', 'seo_footer_code', 'seo_robots_txt',
    ];

    private const REQUEST_KEY = 'seo_page';

    private function setting(string $key): string
    {
        return trim((string) SettingsService::get($key, ''));
    }

    /* ───────────────────────── current page ───────────────────────── */

    /** Called by the page routes, so 404 and other responses never get a page's tags. */
    public static function setCurrent(string $pageKey): void
    {
        request()->attributes->set(self::REQUEST_KEY, $pageKey);
    }

    public function current(): ?SeoPage
    {
        $key = request()->attributes->get(self::REQUEST_KEY);

        if (! is_string($key)) {
            return null;
        }

        return SeoPage::firstWhere('page_key', $key)
            ?? new SeoPage(['page_key' => $key, 'name' => SeoPage::PAGES[$key][1] ?? str($key)->headline()->toString()]);
    }

    /** Page address with the trailing slash WordPress used, e.g. https://hostorio.com/web-hosting/ */
    public static function url(string $slug = ''): string
    {
        return rtrim(url(trim($slug, '/')), '/') . '/';
    }

    /* ───────────────────────── titles & text ───────────────────────── */

    public function siteName(): string
    {
        return $this->setting('seo_site_name') ?: self::DEFAULT_SITE_NAME;
    }

    public function separator(): string
    {
        return $this->setting('seo_separator') ?: '-';
    }

    public function pattern(): string
    {
        return $this->setting('seo_title_pattern') ?: self::DEFAULT_PATTERN;
    }

    /** Fill in %title%, %sep% and %sitename%. */
    public function resolveTitle(?string $template, string $pageName): string
    {
        $template = filled($template) ? $template : $this->pattern();

        $title = strtr($template, [
            '%title%' => $pageName,
            '%sep%' => $this->separator(),
            '%sitename%' => $this->siteName(),
        ]);

        return trim(preg_replace('/\s+/u', ' ', $title));
    }

    public function title(SeoPage $page): string
    {
        return $this->resolveTitle($page->seo_title, (string) $page->name);
    }

    public function description(SeoPage $page): string
    {
        return trim((string) $page->meta_description);
    }

    public function canonical(SeoPage $page): string
    {
        return filled($page->canonical_url) ? (string) $page->canonical_url : self::url($page->slug());
    }

    public function robots(SeoPage $page): string
    {
        if ($this->isHidden()) {
            return 'noindex, nofollow';
        }

        $robots = [$page->robots_follow ? 'follow' : 'nofollow', $page->robots_index ? 'index' : 'noindex'];

        if ($page->robots_index) {
            $robots[] = self::ROBOTS_EXTRA;
        }

        return implode(', ', $robots);
    }

    public function socialTitle(SeoPage $page): string
    {
        return filled($page->og_title) ? $this->resolveTitle($page->og_title, (string) $page->name) : $this->title($page);
    }

    public function socialDescription(SeoPage $page): string
    {
        return trim((string) $page->og_description) ?: $this->description($page);
    }

    /* ───────────────────────── images ───────────────────────── */

    /** @return array{url: string, width: ?int, height: ?int, type: ?string}|null */
    public function image(SeoPage $page): ?array
    {
        $path = $page->og_image ?: $this->setting('seo_default_image');

        return $path ? $this->imageInfo($path) : null;
    }

    public function faviconUrl(): ?string
    {
        $path = $this->setting('seo_favicon');

        return $path ? self::publicUrl($path) : null;
    }

    /** Absolute address of an uploaded file, on the domain the visitor is using. */
    private static function publicUrl(string $path): string
    {
        return asset('storage/' . ltrim($path, '/'));
    }

    /** @return array{url: string, width: ?int, height: ?int, type: ?string} */
    private function imageInfo(string $path): array
    {
        $info = ['url' => self::publicUrl($path), 'width' => null, 'height' => null, 'type' => null];

        try {
            $size = @getimagesize(Storage::disk('public')->path($path));

            if ($size) {
                $info = array_merge($info, ['width' => $size[0], 'height' => $size[1], 'type' => $size['mime'] ?? null]);
            }
        } catch (Throwable) {
            // missing file: still output the URL
        }

        return $info;
    }

    /* ───────────────────────── site-wide ───────────────────────── */

    /** "Hide site from search engines", for the demo / staging site. */
    public function isHidden(): bool
    {
        return filter_var(SettingsService::get('seo_hide_site', false), FILTER_VALIDATE_BOOLEAN);
    }

    /** @return array<string, string> meta name => code */
    public function verificationTags(): array
    {
        $tags = [];

        foreach (self::VERIFICATION as $key => $name) {
            if (($code = self::verificationCode($this->setting($key))) !== '') {
                $tags[$name] = $code;
            }
        }

        return $tags;
    }

    /** Accept either the code or the whole <meta ... content="code"> tag. */
    public static function verificationCode(?string $input): string
    {
        $input = trim((string) $input);

        if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $input, $m)) {
            $input = $m[1];
        }

        return trim(strip_tags($input));
    }

    /** Analytics are left out while the site is hidden, so demo visits are not counted. */
    public function ga4Id(): ?string
    {
        $id = strtoupper($this->setting('seo_ga4_id'));

        return ! $this->isHidden() && preg_match('/^G-[A-Z0-9]+$/', $id) ? $id : null;
    }

    public function gtmId(): ?string
    {
        $id = strtoupper($this->setting('seo_gtm_id'));

        return ! $this->isHidden() && preg_match('/^GTM-[A-Z0-9]+$/', $id) ? $id : null;
    }

    /** Admin-entered HTML, output as is. */
    public function headCode(): string
    {
        return (string) SettingsService::get('seo_head_code', '');
    }

    public function footerCode(): string
    {
        return (string) SettingsService::get('seo_footer_code', '');
    }

    /* ───────────────────────── robots.txt ───────────────────────── */

    public function robotsTxt(): string
    {
        $custom = trim((string) SettingsService::get('seo_robots_txt', ''));

        return ($custom !== '' ? $custom : self::defaultRobotsTxt()) . "\n";
    }

    public static function defaultRobotsTxt(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Disallow: /admin/',
            '',
            'Sitemap: ' . url('sitemap.xml'),
        ]);
    }
}
