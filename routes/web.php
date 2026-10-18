<?php

use App\Http\Controllers\SitemapController;
use App\Services\WhmcsService;
use Illuminate\Support\Facades\Route;

// Sitemap
Route::get('sitemap.xml', [SitemapController::class, 'sitemap']);

// Homepage
Route::get('/', function () {
    return view('pages.home', ['heroPrices' => app(WhmcsService::class)->heroPrices()]);
})->name('home');

// All other pages
Route::get('/{slug}', function ($slug) {
    $viewPath = 'pages.' . str_replace('-', '_', $slug);
    if (view()->exists($viewPath)) {
        return view($viewPath);
    }
    abort(404);
})->name('page');
