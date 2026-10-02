<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Models\Article;
use App\Models\Outlet;
use App\Models\Page;
use App\Models\Product;
use App\Models\Promotion;
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
        'products' => Product::available()
            ->where(function ($query): void {
                $query->whereNull('robots_directive')->orWhere('robots_directive', 'not like', 'noindex%');
            })
            ->orderBy('slug')
            ->get(['slug', 'updated_at']),
        'promotions' => Promotion::available()
            ->where(function ($query): void {
                $query->whereNull('robots_directive')->orWhere('robots_directive', 'not like', 'noindex%');
            })
            ->orderBy('slug')
            ->get(['slug', 'updated_at']),
        'outlets' => Outlet::active()
            ->where(function ($query): void {
                $query->whereNull('robots_directive')->orWhere('robots_directive', 'not like', 'noindex%');
            })
            ->orderBy('slug')
            ->get(['slug', 'updated_at']),
        'articles' => Article::published()
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

Route::get('/menu', [ProductController::class, 'index'])->name('products.index');
Route::get('/menu/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/promo', [PromotionController::class, 'index'])->name('promotions.index');
Route::get('/promo/{slug}', [PromotionController::class, 'show'])->name('promotions.show');
Route::get('/outlet', [OutletController::class, 'index'])->name('outlets.index');
Route::get('/outlet/{slug}', [OutletController::class, 'show'])->name('outlets.show');
Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/galeri', [GalleryController::class, 'index'])->name('gallery.index');

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '^(?!admin(?:/|$)|livewire(?:/|$)|storage(?:/|$)|up$|sitemap\.xml$|robots\.txt$|menu(?:/|$)|promo(?:/|$)|outlet(?:/|$)|galeri(?:/|$)|artikel(?:/|$)|faq(?:/|$)|kontak(?:/|$)).+$')
    ->name('pages.show');
