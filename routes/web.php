<?php

use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', function () {
    return view('pages.home');
})->name('home');

// All other pages
Route::get('/{slug}', function ($slug) {
    $viewPath = 'pages.' . str_replace('-', '_', $slug);
    if (view()->exists($viewPath)) {
        return view($viewPath);
    }
    abort(404);
})->name('page');
