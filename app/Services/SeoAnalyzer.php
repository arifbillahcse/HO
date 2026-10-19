<?php

namespace App\Services;

use App\Models\ComparisonRow;
use App\Models\Product;
use App\Models\SeoPage;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Rank Math-style SEO score (0–100) for a page, from its title, description,
 * focus keyword and the English text of the page.
 */
class SeoAnalyzer
{
    /** Points per check; they add up to 100. */
    private const POINTS = [
        'kw_title' => 15, 'kw_title_start' => 5, 'kw_description' => 10, 'kw_h1' => 10,
        'kw_subheading' => 5, 'kw_intro' => 5, 'kw_density' => 10, 'kw_alt' => 5,
        'title_length' => 8, 'description' => 10, 'content_length' => 7,
        'one_h1' => 4, 'image_alt' => 3, 'internal_links' => 3,
    ];

    /* ───────────────────────── page content ───────────────────────── */

    /** Content facts for a page, from cache only (safe while another view is rendering). */
    public function cachedContent(SeoPage $page): ?array
    {
        return Cache::get($this->cacheKey($page));
    }

    /** Content facts for a page, rendering it if needed. Call outside view rendering (e.g. in mount()). */
    public function content(SeoPage $page): ?array
    {
        return Cache::remember($this->cacheKey($page), now()->addDay(), fn () => $this->extract($this->render($page)));
    }

    private function cacheKey(SeoPage $page): string
    {
        $key = $page->page_key;
        $view = resource_path("views/pages/{$key}.blade.php");

        $signature = implode('|', [
            is_file($view) ? filemtime($view) : 0,
            Product::query()->where('page_key', $key)->max('updated_at'),
            Product::query()->where('page_key', $key)->count(),
            ComparisonRow::query()->where('page_key', $key)->max('updated_at'),
        ]);

        return "seo_content_{$key}_" . md5($signature);
    }

    private function render(SeoPage $page): string
    {
        $view = 'pages.' . $page->page_key;

        if (! view()->exists($view)) {
            return '';
        }

        try {
            $data = $page->page_key === 'home' ? ['heroPrices' => app(WhmcsService::class)->heroPrices()] : [];

            return view($view, $data)->render();
        } catch (Throwable $e) {
            Log::warning("SEO analysis could not render {$view}: " . $e->getMessage());

            return '';
        }
    }

    /** @return array{text: string, words: int, h1: list<string>, subheadings: list<string>, images: list<string|null>, internal_links: int}|null */
    public function extract(string $html): ?array
    {
        if (trim($html) === '') {
            return null;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $main = $xpath->query('//main')->item(0) ?? $xpath->query('//body')->item(0);

        if (! $main) {
            return null;
        }

        foreach (iterator_to_array($xpath->query('.//script|.//style|.//noscript|.//template', $main)) as $node) {
            $node->parentNode->removeChild($node);
        }

        $texts = fn (string $query): array => array_values(array_filter(array_map(
            fn ($node) => self::clean($node->textContent),
            iterator_to_array($xpath->query($query, $main)),
        )));

        $text = self::clean($main->textContent);
        $host = parse_url(url('/'), PHP_URL_HOST);
        $internal = 0;

        foreach ($xpath->query('.//a[@href]', $main) as $link) {
            $href = trim($link->getAttribute('href'));
            $linkHost = parse_url($href, PHP_URL_HOST);

            if ($href !== '' && ! str_starts_with($href, '#') && ($linkHost === null || $linkHost === $host)) {
                $internal++;
            }
        }

        return [
            'text' => $text,
            'words' => self::wordCount($text),
            'h1' => $texts('//h1'),
            'subheadings' => $texts('.//h2|.//h3'),
            'images' => array_map(
                fn ($img) => $img->hasAttribute('alt') ? trim($img->getAttribute('alt')) : null,
                iterator_to_array($xpath->query('.//img', $main)),
            ),
            'internal_links' => $internal,
        ];
    }

    /* ───────────────────────── score ───────────────────────── */

    /**
     * @param  array{title: string, description: string, keyword: ?string, indexed: bool}  $input
     * @return array{score: int, checks: list<array{group: string, status: string, label: string, detail: string}>}
     */
    public function analyze(array $input, ?array $content): array
    {
        $keyword = self::clean(explode(',', (string) $input['keyword'])[0] ?? '');
        $title = self::clean($input['title']);
        $description = self::clean($input['description']);
        $checks = [];

        $add = function (string $id, string $group, string $status, string $label, string $detail = '') use (&$checks): void {
            $checks[$id] = compact('group', 'status', 'label', 'detail');
        };

        /* focus keyword */
        if ($keyword === '') {
            foreach (['kw_title', 'kw_title_start', 'kw_description', 'kw_h1', 'kw_subheading', 'kw_intro', 'kw_density', 'kw_alt'] as $id) {
                $checks[$id] = ['group' => 'Focus keyword', 'status' => 'fail', 'label' => '', 'detail' => '', 'hidden' => true];
            }
            $add('kw_missing', 'Focus keyword', 'fail', 'Add a focus keyword', 'The phrase you want this page to rank for. It unlocks 8 more checks worth 65 points.');
        } else {
            $inTitle = self::contains($title, $keyword);
            $add('kw_title', 'Focus keyword', $inTitle ? 'ok' : 'fail', $inTitle ? 'Focus keyword is in the SEO title' : 'Add the focus keyword to the SEO title');

            $position = mb_stripos($title, $keyword);
            $nearStart = $position !== false && $position <= mb_strlen($title) / 2;
            $add('kw_title_start', 'Focus keyword', $nearStart ? 'ok' : 'warn', $nearStart ? 'Focus keyword is near the start of the title' : 'Move the focus keyword towards the start of the title');

            $inDescription = self::contains($description, $keyword);
            $add('kw_description', 'Focus keyword', $inDescription ? 'ok' : 'fail', $inDescription ? 'Focus keyword is in the meta description' : 'Add the focus keyword to the meta description');

            if ($content === null) {
                foreach (['kw_h1', 'kw_subheading', 'kw_intro', 'kw_density', 'kw_alt'] as $id) {
                    $add($id, 'Focus keyword', 'warn', 'Page text could not be read', 'Open the page once, then reload this screen.');
                }
            } else {
                $inH1 = collect($content['h1'])->contains(fn ($h) => self::contains($h, $keyword));
                $add('kw_h1', 'Focus keyword', $inH1 ? 'ok' : 'fail', $inH1 ? 'Focus keyword is in the main heading (H1)' : 'The main heading (H1) does not contain the focus keyword', $content['h1'][0] ?? '');

                $inSub = collect($content['subheadings'])->contains(fn ($h) => self::contains($h, $keyword));
                $add('kw_subheading', 'Focus keyword', $inSub ? 'ok' : 'warn', $inSub ? 'Focus keyword is in a subheading (H2/H3)' : 'No subheading (H2/H3) contains the focus keyword');

                $words = preg_split('/\s+/u', $content['text']);
                $intro = implode(' ', array_slice($words, 0, max(60, (int) ceil(count($words) * 0.1))));
                $inIntro = self::contains($intro, $keyword);
                $add('kw_intro', 'Focus keyword', $inIntro ? 'ok' : 'warn', $inIntro ? 'Focus keyword appears at the start of the page' : 'Use the focus keyword in the first part of the page text');

                $count = self::occurrences($content['text'], $keyword);
                $density = $content['words'] > 0 ? round($count / $content['words'] * 100, 2) : 0;
                $add('kw_density', 'Focus keyword', $count === 0 ? 'fail' : ($density >= 0.5 && $density <= 2.5 ? 'ok' : 'warn'),
                    "Keyword density {$density}% ({$count} times)",
                    $count === 0 ? 'The focus keyword is not in the page text.' : ($density < 0.5 ? 'A little low: 0.5–2.5% works best.' : ($density > 2.5 ? 'Too high: it can look like keyword stuffing.' : 'Good.')));

                $inAlt = collect($content['images'])->contains(fn ($alt) => $alt !== null && self::contains($alt, $keyword));
                $add('kw_alt', 'Focus keyword', $inAlt ? 'ok' : 'warn', $inAlt ? 'Focus keyword is in an image description (alt text)' : 'No image description (alt text) contains the focus keyword');
            }
        }

        /* basics */
        $titleLength = mb_strlen($title);
        $add('title_length', 'Basics', $titleLength >= 30 && $titleLength <= 60 ? 'ok' : ($titleLength > 0 && $titleLength <= 70 ? 'warn' : 'fail'),
            "SEO title is {$titleLength} characters", '30–60 works best; Google cuts titles after about 60.');

        $descriptionLength = mb_strlen($description);
        $add('description', 'Basics', $descriptionLength === 0 ? 'fail' : ($descriptionLength >= 120 && $descriptionLength <= 160 ? 'ok' : 'warn'),
            $descriptionLength === 0 ? 'No meta description' : "Meta description is {$descriptionLength} characters", '120–160 works best.');

        if ($content !== null) {
            $words = $content['words'];
            $add('content_length', 'Basics', $words >= 600 ? 'ok' : ($words >= 300 ? 'warn' : 'fail'), "The page has {$words} words", '600+ words gives Google more to rank.');

            $h1Count = count($content['h1']);
            $add('one_h1', 'Basics', $h1Count === 1 ? 'ok' : ($h1Count === 0 ? 'fail' : 'warn'),
                $h1Count === 1 ? 'The page has one main heading (H1)' : "The page has {$h1Count} main headings (H1)", 'Exactly one is best.');

            $missingAlt = count(array_filter($content['images'], fn ($alt) => $alt === null || $alt === ''));
            $add('image_alt', 'Basics', $missingAlt === 0 ? 'ok' : 'warn',
                $missingAlt === 0 ? 'Every image has a description (alt text)' : "{$missingAlt} images have no description (alt text)");

            $add('internal_links', 'Basics', $content['internal_links'] > 0 ? 'ok' : 'warn', "{$content['internal_links']} links to other pages of the site");
        } else {
            foreach (['content_length', 'one_h1', 'image_alt', 'internal_links'] as $id) {
                $add($id, 'Basics', 'warn', 'Page text could not be read');
            }
        }

        $score = 0;
        foreach (self::POINTS as $id => $points) {
            $status = $checks[$id]['status'] ?? 'fail';
            $score += $status === 'ok' ? $points : ($status === 'warn' ? $points / 2 : 0);
        }

        $visible = array_values(array_filter($checks, fn ($check) => ! ($check['hidden'] ?? false)));

        if (! $input['indexed']) {
            array_unshift($visible, ['group' => 'Basics', 'status' => 'warn', 'label' => 'This page is set to noindex', 'detail' => 'It will not appear in Google, whatever its score.']);
        }

        return ['score' => (int) round($score), 'checks' => $visible];
    }

    /** Score for a saved page, using cached content only. */
    public function scoreFor(SeoPage $page, ?array $content): array
    {
        return $this->analyze([
            'title' => app(Seo::class)->title($page),
            'description' => (string) $page->meta_description,
            'keyword' => $page->focus_keyword,
            'indexed' => (bool) $page->robots_index,
        ], $content);
    }

    public static function color(int $score): string
    {
        return $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
    }

    /* ───────────────────────── text helpers ───────────────────────── */

    private static function clean(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function wordCount(string $text): int
    {
        return preg_match_all('/[\p{L}\p{N}]+(?:[\'’\-][\p{L}\p{N}]+)*/u', $text);
    }

    private static function contains(string $haystack, string $keyword): bool
    {
        return self::occurrences($haystack, $keyword) > 0;
    }

    /** Whole-phrase, case-insensitive matches ("host" does not match "hosting"). */
    private static function occurrences(string $haystack, string $keyword): int
    {
        $pattern = '/(?<![\p{L}\p{N}])' . str_replace(' ', '\s+', preg_quote($keyword, '/')) . '(?![\p{L}\p{N}])/iu';

        return (int) preg_match_all($pattern, $haystack);
    }
}
