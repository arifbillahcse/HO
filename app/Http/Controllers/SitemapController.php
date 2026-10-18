<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $pages = [
            ['url' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => url('/terms-and-conditions'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/privacy-policy'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/about-us'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => url('/web-hosting'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => url('/turbo-hosting'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => url('/reseller-hosting'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => url('/student-hosting'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => url('/vps'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => url('/business-email'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => url('/offer'), 'priority' => '0.9', 'changefreq' => 'weekly'],
        ];

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($pages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($page['url']) . "</loc>\n";
            $xml .= "    <lastmod>" . now()->toAtomString() . "</lastmod>\n";
            $xml .= "    <changefreq>" . $page['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $page['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>";

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }
}
