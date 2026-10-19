<?php

namespace App\Http\Controllers;

use App\Models\ComparisonRow;
use App\Models\Product;
use App\Models\SeoPage;
use App\Services\Seo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    private const PRIORITY = [
        'home' => ['1.0', 'weekly'],
        'web_hosting' => ['0.9', 'weekly'],
        'turbo_hosting' => ['0.9', 'weekly'],
        'offer' => ['0.9', 'weekly'],
        'domain_registration' => ['0.9', 'weekly'],
        'about_us' => ['0.9', 'monthly'],
        'privacy_policy' => ['0.8', 'monthly'],
        'terms_and_conditions' => ['0.8', 'monthly'],
    ];

    public function sitemap()
    {
        // Pages set to "noindex" or pointing their canonical elsewhere are left out.
        $pages = SeoPage::query()
            ->where('robots_index', true)
            ->where(fn ($q) => $q->whereNull('canonical_url')->orWhere('canonical_url', ''))
            ->orderBy('sort_order')
            ->get();

        $products = Product::query()->groupBy('page_key')->selectRaw('page_key, max(updated_at) as changed')->pluck('changed', 'page_key');
        $rows = ComparisonRow::query()->groupBy('page_key')->selectRaw('page_key, max(updated_at) as changed')->pluck('changed', 'page_key');

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($pages as $page) {
            [$priority, $changefreq] = self::PRIORITY[$page->page_key] ?? ['0.8', 'weekly'];

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars(Seo::url($page->slug())) . "</loc>\n";
            $xml .= "    <lastmod>" . $this->lastModified($page, $products[$page->page_key] ?? null, $rows[$page->page_key] ?? null)->toAtomString() . "</lastmod>\n";
            $xml .= "    <changefreq>" . $changefreq . "</changefreq>\n";
            $xml .= "    <priority>" . $priority . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>";

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }

    /** Latest change to the page: its SEO settings, plans, comparison table or template. */
    private function lastModified(SeoPage $page, ?string $products, ?string $rows): Carbon
    {
        $view = resource_path('views/pages/' . $page->page_key . '.blade.php');

        return collect([
            $page->updated_at,
            $products ? Carbon::parse($products) : null,
            $rows ? Carbon::parse($rows) : null,
            is_file($view) ? Carbon::createFromTimestamp(filemtime($view)) : null,
        ])->filter()->max() ?? now();
    }
}
