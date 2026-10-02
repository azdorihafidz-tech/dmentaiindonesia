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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_navigation_and_empty_listing_pages_render_with_seo(): void
    {
        foreach (['/', '/menu', '/promo', '/outlet', '/galeri', '/artikel', '/faq'] as $path) {
            $response = $this->get($path)->assertOk()->assertSee('name="description"', false)->assertSee('rel="canonical"', false);
        }

        $response->assertSee('aria-label="Navigasi utama"', false)
            ->assertSee(route('products.index'))
            ->assertDontSee('Masuk admin');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('/sitemap.xml');
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('gallery.index'))->assertSee(route('faqs.index'));
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

        $this->get('/')->assertOk()->assertSee('Nama Brand Uji')->assertSee('Logo D’Mentai')->assertSee('https://wa.me/08123456789')->assertDontSee('Instagram');
    }

    public function test_public_product_category_filter_and_detail_are_usable_without_invented_price(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Kategori Uji', 'slug' => 'kategori-uji', 'status' => 'active']);
        Product::query()->create(['category_id' => $category->id, 'name' => 'Produk Filter Uji', 'slug' => 'produk-filter-uji', 'status' => 'active']);
        $this->get('/menu?kategori=kategori-uji')->assertOk()->assertSee('Produk Filter Uji');
        $this->get('/menu/produk-filter-uji')->assertOk()->assertSee('Produk Filter Uji')->assertDontSee('Rp ');
    }

    public function test_missing_public_page_uses_brand_consistent_not_found_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }
}
