<?php

namespace App\Providers;

use App\Services\SiteSettings;
use App\Services\GeoIpCountryLookup;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\Outlet;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Testimonial;
use App\Models\User;
use App\Observers\ContentActivityObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(SiteSettings::class);
        $this->app->scoped(GeoIpCountryLookup::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            Article::class,
            ArticleCategory::class,
            Faq::class,
            GalleryItem::class,
            Media::class,
            Outlet::class,
            Page::class,
            Product::class,
            ProductCategory::class,
            Promotion::class,
            Testimonial::class,
            User::class,
        ] as $model) {
            $model::observe(ContentActivityObserver::class);
        }
    }
}
