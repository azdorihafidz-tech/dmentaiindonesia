<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Outlet;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SeoManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_navigation_and_empty_listing_pages_render_with_seo(): void
    {
        foreach (['/', '/menu', '/promo', '/outlet', '/galeri', '/artikel', '/faq'] as $path) {
            $response = $this->get($path)->assertOk()->assertSee('<title>', false)
                ->assertSee('name="description"', false)
                ->assertSee('rel="canonical"', false)
                ->assertSee('property="og:title"', false)
                ->assertDontSee('rel="icon"', false)
                ->assertDontSee('class="brand-logo"', false)
                ->assertSee('name="twitter:title"', false);
        }

        $response->assertSee('aria-label="Navigasi utama"', false)
            ->assertSee(route('products.index'))
            ->assertDontSee('Masuk admin');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('/sitemap.xml');
        $sitemap = $this->get('/sitemap.xml')->assertOk()->assertSee(route('gallery.index'))->assertSee(route('faqs.index'));
        preg_match_all('~<loc>(.*?)</loc>~', $sitemap->getContent(), $locations);
        $this->assertCount(count(array_unique($locations[1])), $locations[1]);
        $this->assertStringNotContainsString('/admin', $sitemap->getContent());
    }

    public function test_homepage_uses_published_page_navigation_and_only_public_cms_content(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Menu Uji', 'slug' => 'menu-uji', 'status' => 'active']);
        Product::query()->create(['category_id' => $category->id, 'name' => 'Produk Unggulan Uji', 'slug' => 'produk-unggulan-uji', 'status' => 'active', 'featured' => true]);
        Product::query()->create(['category_id' => $category->id, 'name' => 'Produk Nonaktif Uji', 'slug' => 'produk-nonaktif-uji', 'status' => 'inactive', 'featured' => true]);
        Promotion::query()->create(['title' => 'Promo Unggulan Uji', 'slug' => 'promo-unggulan-uji', 'status' => 'active', 'featured' => true]);
        Promotion::query()->create(['title' => 'Promo Kedaluwarsa Uji', 'slug' => 'promo-kedaluwarsa-uji', 'status' => 'active', 'featured' => true, 'end_at' => now()->subDay()]);
        Outlet::query()->create(['name' => 'Outlet Aktif Uji', 'slug' => 'outlet-aktif-uji', 'status' => 'active', 'featured' => true]);
        Outlet::query()->create(['name' => 'Outlet Nonaktif Uji', 'slug' => 'outlet-nonaktif-uji', 'status' => 'inactive', 'featured' => true]);
        Article::query()->create(['title' => 'Artikel Terbit Uji', 'slug' => 'artikel-terbit-uji', 'status' => 'published']);
        Article::query()->create(['title' => 'Artikel Draft Uji', 'slug' => 'artikel-draft-uji', 'status' => 'draft']);
        Faq::query()->create(['question' => 'Pertanyaan aktif uji?', 'answer' => 'Jawaban yang sudah disediakan.', 'status' => 'active']);
        Faq::query()->create(['question' => 'Pertanyaan tersembunyi uji?', 'answer' => 'Tidak dipublikasikan.', 'status' => 'inactive']);
        Page::query()->create(['title' => 'Tentang D’Mentai', 'slug' => 'tentang-kami', 'content' => '<p>Konten dari CMS.</p>', 'status' => 'published']);
        Page::query()->create(['title' => 'Hubungi D’Mentai', 'slug' => 'kontak', 'content' => '<p>Kontak dari CMS.</p>', 'status' => 'published']);
        Page::query()->create(['title' => 'Halaman Draft', 'slug' => 'halaman-draft', 'status' => 'draft']);

        $this->get('/')->assertOk()->assertSee('Produk Unggulan Uji')->assertDontSee('Produk Nonaktif Uji')
            ->assertSee('Promo Unggulan Uji')->assertDontSee('Promo Kedaluwarsa Uji')
            ->assertSee('Outlet Aktif Uji')->assertDontSee('Outlet Nonaktif Uji')
            ->assertSee('Artikel Terbit Uji')->assertDontSee('Artikel Draft Uji')
            ->assertSee('Pertanyaan aktif uji?')->assertDontSee('Pertanyaan tersembunyi uji?')
            ->assertSee('Tentang D’Mentai')->assertDontSee('Halaman Draft');
        $this->get('/tentang-kami')->assertOk()->assertSee('Konten dari CMS.');
        $this->get('/kontak')->assertOk()->assertSee('Kontak dari CMS.')->assertSee('Hubungi D’Mentai');
        $this->get('/halaman-draft')->assertNotFound();
    }

    public function test_homepage_uses_configured_brand_contact_and_logo_without_rendering_empty_social_links(): void
    {
        $logo = Media::query()->create(['disk' => 'public', 'path' => 'brand/logo.svg', 'original_name' => 'logo.svg', 'mime_type' => 'image/svg+xml', 'file_size' => 42, 'alt_text' => 'Logo D’Mentai']);
        SiteSetting::query()->create(['group' => 'brand', 'key' => 'brand.site_name', 'value' => 'Nama Brand Uji']);
        SiteSetting::query()->create(['group' => 'brand', 'key' => 'brand.logo_media_id', 'value' => (string) $logo->id]);
        SiteSetting::query()->create(['group' => 'contact', 'key' => 'contact.whatsapp', 'value' => '08123456789']);
        SiteSetting::query()->create(['group' => 'brand', 'key' => 'brand.description', 'value' => 'Deskripsi bisnis yang dikonfigurasi.']);
        SiteSetting::query()->create(['group' => 'location', 'key' => 'location.city', 'value' => 'Kota Uji']);
        SiteSetting::query()->create(['group' => 'analytics', 'key' => 'analytics.search_console_verification', 'value' => 'verification-token']);

        $this->get('/')->assertSee('Deskripsi bisnis yang dikonfigurasi.')
            ->assertSee('Kota Uji')
            ->assertSee('name="google-site-verification" content="verification-token"', false);

        $this->get('/')->assertOk()->assertSee('Nama Brand Uji')->assertSee('Logo D’Mentai')->assertSee('class="brand-logo"', false)
            ->assertSee($logo->url, false)->assertSee('https://wa.me/08123456789')->assertDontSee('Instagram')
            ->assertDontSee('class="brand-mark"', false);
    }

    public function test_homepage_supports_separate_default_open_graph_title_and_description(): void
    {
        SiteSetting::query()->create(['group' => 'seo', 'key' => 'seo.default_title', 'value' => 'Judul halaman demo']);
        SiteSetting::query()->create(['group' => 'seo', 'key' => 'seo.meta_description', 'value' => 'Deskripsi halaman demo']);
        SiteSetting::query()->create(['group' => 'seo', 'key' => 'seo.og_title', 'value' => 'Judul sosial demo']);
        SiteSetting::query()->create(['group' => 'seo', 'key' => 'seo.og_description', 'value' => 'Deskripsi sosial demo']);

        $this->get('/')->assertOk()
            ->assertSee('<title>Judul halaman demo</title>', false)
            ->assertSee('name="description" content="Deskripsi halaman demo"', false)
            ->assertSee('property="og:title" content="Judul sosial demo"', false)
            ->assertSee('property="og:description" content="Deskripsi sosial demo"', false);
    }

    public function test_default_open_graph_image_falls_back_but_page_specific_image_has_priority(): void
    {
        $default = Media::query()->create([
            'disk' => 'public', 'path' => 'brand/default-og.png', 'original_name' => 'default-og.png',
            'mime_type' => 'image/png', 'file_size' => 100, 'folder' => 'brand',
        ]);
        $specific = Media::query()->create([
            'disk' => 'public', 'path' => 'brand/page-og.webp', 'original_name' => 'page-og.webp',
            'mime_type' => 'image/webp', 'file_size' => 100, 'folder' => 'brand',
        ]);
        $featured = Media::query()->create([
            'disk' => 'public', 'path' => 'brand/page-featured.png', 'original_name' => 'page-featured.png',
            'mime_type' => 'image/png', 'file_size' => 100, 'folder' => 'brand',
        ]);
        SiteSetting::query()->create(['group' => 'seo', 'key' => 'seo.default_og_media_id', 'value' => (string) $default->id]);

        $this->get('/menu')->assertOk()
            ->assertSee('property="og:image" content="'.$default->url.'"', false);

        Page::query()->create([
            'title' => 'Halaman OG Khusus', 'slug' => 'halaman-og-khusus', 'featured_media_id' => $featured->id,
            'og_media_id' => $specific->id, 'status' => 'published',
        ]);

        $response = $this->get('/halaman-og-khusus')->assertOk()
            ->assertSee('property="og:image" content="'.$specific->url.'"', false)
            ->assertDontSee('property="og:image" content="'.$default->url.'"', false);

        $this->assertSame(1, substr_count($response->getContent(), 'property="og:image"'));
    }

    public function test_public_product_category_filter_and_detail_are_usable_without_invented_price(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Kategori Uji', 'slug' => 'kategori-uji', 'status' => 'active']);
        ProductCategory::query()->create(['name' => 'Kategori Kosong', 'slug' => 'kategori-kosong', 'status' => 'active']);
        Product::query()->create(['category_id' => $category->id, 'name' => 'Produk Filter Uji', 'slug' => 'produk-filter-uji', 'status' => 'active']);
        $this->get('/menu?kategori=kategori-uji')->assertOk()->assertSee('Produk Filter Uji');
        $this->get('/menu?kategori=kategori-kosong')->assertOk()->assertSee('Belum ada menu dalam kategori ini.');
        $this->get('/menu/produk-filter-uji')->assertOk()->assertSee('Produk Filter Uji')->assertDontSee('Rp ');
        $this->get('/menu?kategori=kategori-uji')->assertSee('rel="canonical" href="'.route('products.index').'"', false);
    }

    public function test_missing_public_page_uses_brand_consistent_not_found_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }

    public function test_homepage_json_ld_uses_configured_organization_data_and_valid_json(): void
    {
        $logo = Media::query()->create([
            'disk' => 'public', 'path' => 'brand/official-logo.png', 'original_name' => 'official-logo.png',
            'mime_type' => 'image/png', 'file_size' => 100, 'alt_text' => 'Logo resmi',
        ]);
        SiteSetting::query()->create(['group' => 'brand', 'key' => 'brand.site_name', 'value' => 'D’Mentai Uji']);
        SiteSetting::query()->create(['group' => 'brand', 'key' => 'brand.logo_media_id', 'value' => (string) $logo->id]);
        SiteSetting::query()->create(['group' => 'brand', 'key' => 'brand.description', 'value' => '</script><script>alert(1)</script>']);
        SiteSetting::query()->create(['group' => 'social', 'key' => 'social.instagram', 'value' => 'https://instagram.com/dmentai-test']);

        $response = $this->get('/')->assertOk()->assertSee('application/ld+json')->assertDontSee('</script><script>alert(1)', false);
        $graph = $this->structuredData($response)['@graph'];
        $organization = collect($graph)->firstWhere('@type', 'Organization');
        $website = collect($graph)->firstWhere('@type', 'WebSite');

        $this->assertSame('D’Mentai Uji', $organization['name']);
        $this->assertSame($logo->url, $organization['logo']['url']);
        $this->assertSame(['https://instagram.com/dmentai-test'], $organization['sameAs']);
        $this->assertSame(route('home'), $website['url']);
    }

    public function test_content_json_ld_only_contains_supported_product_article_and_outlet_data(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Kategori Schema', 'slug' => 'kategori-schema', 'status' => 'active']);
        $product = Product::query()->create([
            'category_id' => $category->id, 'name' => 'Produk Schema', 'slug' => 'produk-schema', 'status' => 'active',
        ]);
        $productGraph = $this->structuredData($this->get('/menu/produk-schema')->assertOk())['@graph'];
        $this->assertNotContains('Product', array_column($productGraph, '@type'));

        $product->update(['price' => '12500.00', 'promotional_price' => '10000.00']);
        $productGraph = $this->structuredData($this->get('/menu/produk-schema')->assertOk())['@graph'];
        $productSchema = collect($productGraph)->firstWhere('@type', 'Product');
        $this->assertSame('10000.00', $productSchema['offers']['price']);
        $this->assertSame('IDR', $productSchema['offers']['priceCurrency']);
        $this->assertArrayNotHasKey('availability', $productSchema['offers']);
        $this->assertNotContains('AggregateRating', array_column($productGraph, '@type'));
        $this->assertCount(3, collect($productGraph)->firstWhere('@type', 'BreadcrumbList')['itemListElement']);

        $author = User::factory()->create(['name' => 'Penulis Terdaftar']);
        Article::query()->create([
            'title' => 'Artikel Schema', 'slug' => 'artikel-schema', 'status' => 'published', 'author_id' => $author->id,
        ]);
        $articleGraph = $this->structuredData($this->get('/artikel/artikel-schema')->assertOk())['@graph'];
        $articleSchema = collect($articleGraph)->firstWhere('@type', 'Article');
        $this->assertSame('Artikel Schema', $articleSchema['headline']);
        $this->assertSame('Penulis Terdaftar', $articleSchema['author']['name']);

        Outlet::query()->create(['name' => 'Outlet Tanpa Alamat', 'slug' => 'outlet-tanpa-alamat', 'status' => 'active']);
        $outletGraph = $this->structuredData($this->get('/outlet/outlet-tanpa-alamat')->assertOk())['@graph'];
        $this->assertNotContains('LocalBusiness', array_column($outletGraph, '@type'));

        Outlet::query()->create([
            'name' => 'Outlet Beralamat', 'slug' => 'outlet-beralamat', 'address' => 'Alamat dari CMS',
            'city' => 'Kota Data', 'province' => 'Provinsi Data', 'country' => 'Indonesia', 'phone' => '0215550100',
            'status' => 'active',
        ]);
        $outletGraph = $this->structuredData($this->get('/outlet/outlet-beralamat')->assertOk())['@graph'];
        $business = collect($outletGraph)->firstWhere('@type', 'LocalBusiness');
        $this->assertSame('Alamat dari CMS', $business['address']['streetAddress']);
        $this->assertSame('Kota Data', $business['address']['addressLocality']);
        $this->assertSame('0215550100', $business['telephone']);
        $this->assertArrayNotHasKey('geo', $business);
        $this->assertArrayNotHasKey('openingHours', $business);
    }

    public function test_debug_disabled_500_page_hides_exception_details(): void
    {
        config(['app.debug' => false]);
        Route::post('/__phase5-test-error', fn () => throw new \RuntimeException('phase5-secret-marker'));

        $this->post('/__phase5-test-error')->assertStatus(500)
            ->assertSee('Terjadi gangguan')
            ->assertDontSee('phase5-secret-marker')
            ->assertDontSee('Stack trace')
            ->assertDontSee('D:\\xampp\\htdocs');
    }

    public function test_seo_canonicals_use_configured_base_url_without_query_parameters(): void
    {
        config(['app.url' => 'https://www.dmentai-test.example/']);
        app('url')->forceRootUrl(config('app.url'));
        app('url')->forceScheme('https');

        $home = app(SeoManager::class)->homepage();
        $listing = app(SeoManager::class)->forListing('Menu', 'products.index');

        $this->assertSame('https://www.dmentai-test.example', $home->canonical);
        $this->assertSame('https://www.dmentai-test.example/menu', $listing->canonical);
        $this->assertStringNotContainsString('?', $listing->canonical);
    }

    /** @return array<string, mixed> */
    private function structuredData(TestResponse $response): array
    {
        $matches = [];
        $this->assertSame(1, preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $response->getContent(), $matches));

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
