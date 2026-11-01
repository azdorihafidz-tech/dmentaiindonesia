<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Middleware\TrackPageView;
use App\Models\Article;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Outlet;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Testimonial;
use App\Services\SeoManager;
use App\Services\PageViewMetrics;
use App\Services\SiteSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function (SeoManager $seo, SiteSettings $settings, PageViewMetrics $metrics) {
    return view('welcome', [
        'metadata' => $seo->homepage(),
        'settings' => $settings,
        'products' => Product::available()->where('featured', true)->with(['category', 'image'])->orderBy('sort_order')->limit(4)->get(),
        'categories' => ProductCategory::active()->with('image')->orderBy('sort_order')->limit(8)->get(),
        'promotion' => Promotion::available()->where('featured', true)->with('banner')->orderBy('sort_order')->first(),
        'outlets' => Outlet::active()->where('featured', true)->with('image')->orderBy('sort_order')->limit(3)->get(),
        'gallery' => GalleryItem::active()->with('image')->orderBy('sort_order')->limit(6)->get(),
        'articles' => Article::published()->with(['category', 'featuredMedia'])->orderByDesc('published_at')->limit(3)->get(),
        'faqs' => Faq::active()->orderBy('sort_order')->orderBy('id')->limit(4)->get(),
        'testimonials' => Testimonial::active()->with('image')->orderByDesc('featured')->orderBy('sort_order')->limit(6)->get(),
        'visitorStats' => $metrics->publicSummary(),
    ]);
})->middleware(TrackPageView::class)->name('home');

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
        'listings' => [
            route('products.index'), route('promotions.index'), route('outlets.index'),
            route('articles.index'), route('gallery.index'), route('faqs.index'),
        ],
    ])->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $sitemap = rtrim(config('app.url'), '/').route('sitemap', [], false);

    return response("User-agent: *\nDisallow: /admin\nDisallow: /livewire\nSitemap: {$sitemap}\n", 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');

Route::get('/menu', [ProductController::class, 'index'])->middleware(TrackPageView::class)->name('products.index');
Route::get('/menu/{slug}', [ProductController::class, 'show'])->middleware(TrackPageView::class)->name('products.show');
Route::get('/promo', [PromotionController::class, 'index'])->middleware(TrackPageView::class)->name('promotions.index');
Route::get('/promo/{slug}', [PromotionController::class, 'show'])->middleware(TrackPageView::class)->name('promotions.show');
Route::get('/outlet', [OutletController::class, 'index'])->middleware(TrackPageView::class)->name('outlets.index');
Route::get('/outlet/{slug}', [OutletController::class, 'show'])->middleware(TrackPageView::class)->name('outlets.show');
Route::get('/artikel', [ArticleController::class, 'index'])->middleware(TrackPageView::class)->name('articles.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->middleware(TrackPageView::class)->name('articles.show');
Route::get('/galeri', [GalleryController::class, 'index'])->middleware(TrackPageView::class)->name('gallery.index');
Route::get('/faq', [FaqController::class, 'index'])->middleware(TrackPageView::class)->name('faqs.index');

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '^(?!admin(?:/|$)|livewire(?:/|$)|storage(?:/|$)|up$|sitemap\.xml$|robots\.txt$|menu(?:/|$)|promo(?:/|$)|outlet(?:/|$)|galeri(?:/|$)|artikel(?:/|$)|faq(?:/|$)).+$')
    ->middleware(TrackPageView::class)
    ->name('pages.show');
