<?php

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Services\SeoManager;
use App\Services\SiteSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function (SeoManager $seo, SiteSettings $settings) {
    return view('welcome', ['metadata' => $seo->homepage(), 'settings' => $settings]);
})->name('home');

Route::get('/sitemap.xml', function () {
    return response()->view('sitemap', [
        'pages' => Page::published()
            ->where(function ($query): void {
                $query->whereNull('robots_directive')->orWhere('robots_directive', 'not like', 'noindex%');
            })
            ->orderBy('slug')
            ->get(['slug', 'updated_at']),
    ])->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $sitemap = rtrim(config('app.url'), '/').route('sitemap', [], false);

    return response("User-agent: *\nDisallow: /admin\nDisallow: /livewire\nSitemap: {$sitemap}\n", 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '^(?!admin(?:/|$)|livewire(?:/|$)|storage(?:/|$)|up$|sitemap\.xml$|robots\.txt$|menu(?:/|$)|promo(?:/|$)|outlet(?:/|$)|galeri(?:/|$)|artikel(?:/|$)|faq(?:/|$)|kontak(?:/|$)).+$')
    ->name('pages.show');
