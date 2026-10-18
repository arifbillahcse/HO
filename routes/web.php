<?php

use App\Http\Controllers\SitemapController;
use App\Models\SeoPage;
use App\Services\Seo;
use App\Services\WhmcsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sitemap
Route::get('sitemap.xml', [SitemapController::class, 'sitemap']);

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
