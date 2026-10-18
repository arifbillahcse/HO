<?php

use App\Http\Controllers\SitemapController;
use App\Models\SeoPage;
use App\Services\Seo;
use App\Services\WhmcsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sitemap and robots.txt
Route::get('sitemap.xml', [SitemapController::class, 'sitemap']);
Route::get('robots.txt', fn () => response(app(Seo::class)->robotsTxt(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']));

// Old Rank Math sitemap addresses still known to search engines
foreach (['sitemap_index.xml', 'page-sitemap.xml', 'post-sitemap.xml', 'category-sitemap.xml', 'local-sitemap.xml'] as $old) {
    Route::get($old, fn () => redirect('sitemap.xml', 301));
}

// Homepage
Route::get('/', function () {
    Seo::setCurrent('home');

    return view('pages.home', ['heroPrices' => app(WhmcsService::class)->heroPrices()]);
})->name('home');

// All other pages. Addresses end in "/", as they did on WordPress.
Route::get('/{slug}', function (Request $request, $slug) {
    $key = SeoPage::keyForSlug($slug);
    $viewPath = 'pages.' . $key;

    if (! view()->exists($viewPath)) {
        abort(404);
    }

    if ($key === 'home') {
        return redirect()->to(Seo::url(), 301); // one address per page
    }

    if (! str_ends_with($request->getPathInfo(), '/')) {
        $query = $request->getQueryString();

        return redirect()->to(Seo::url($slug) . ($query ? "?{$query}" : ''), 301);
    }

    Seo::setCurrent($key);

    return view($viewPath);
})->name('page');
