<?php

namespace Tests\Feature;

use App\Filament\Pages\WebsiteSettings;
use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Models\Media;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\BrandAssetManager;
use App\Services\PageContentSanitizer;
use App\Services\SeoManager;
use App\Services\SiteSettings;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CoreCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_can_be_saved_and_retrieved(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $settings = app(SiteSettings::class);
        $settings->save(['brand' => ['site_name' => "D'Mentai", 'tagline' => 'Dimsum']]);

        $this->assertSame("D'Mentai", app(SiteSettings::class)->get('brand.site_name'));
        $this->assertSame('Dimsum', SiteSetting::query()->where('key', 'brand.tagline')->value('value'));
    }

    public function test_public_pages_use_the_dmentai_favicon_when_no_custom_icon_is_configured(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<link rel="icon" type="image/svg+xml" href="'.asset('images/brand/dmentai-favicon.svg').'?v=1">', false)
            ->assertSee('<link rel="icon" type="image/png" sizes="64x64" href="'.asset('images/brand/dmentai-favicon.png').'?v=1">', false);
    }

    public function test_only_admin_can_open_cms_pages(): void
    {
        $this->get('/admin/website-settings')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/pages')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/media')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/website-settings')->assertForbidden();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/website-settings')->assertOk();
        $this->actingAs($admin)->get('/admin/pages')->assertOk();
        $this->actingAs($admin)->get('/admin/media')->assertOk();
    }

    public function test_admin_can_save_website_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/website-settings')->assertOk()
            ->assertSee('BRAND ASSETS')
            ->assertSee('Logo utama yang digunakan pada header website.')
            ->assertSee('Gambar yang digunakan pada tab browser.')
            ->assertSee('Favicon yang digunakan')
            ->assertSee('Gambar default yang digunakan ketika halaman tidak memiliki OG image khusus.');

        Livewire::actingAs($admin)
            ->test(WebsiteSettings::class)
            ->set('data.brand.site_name', 'D’Mentai Baru')
            ->set('data.brand.description', 'Deskripsi bisnis yang dikonfigurasi.')
            ->set('data.location.city', 'Kota Uji')
            ->set('data.location.province', 'Provinsi Uji')
            ->set('data.location.country', 'Indonesia')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'brand.site_name', 'value' => 'D’Mentai Baru']);
        $this->assertDatabaseHas('site_settings', ['key' => 'brand.description', 'value' => 'Deskripsi bisnis yang dikonfigurasi.']);
        $this->assertDatabaseHas('site_settings', ['key' => 'location.city', 'value' => 'Kota Uji']);
        $this->assertDatabaseHas('site_settings', ['key' => 'location.province', 'value' => 'Provinsi Uji']);
        $this->assertDatabaseHas('site_settings', ['key' => 'location.country', 'value' => 'Indonesia']);

        Livewire::actingAs($admin)
            ->test(WebsiteSettings::class)
            ->set('data.contact.email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['data.contact.email']);
    }

    public function test_website_settings_uploads_logo_favicon_and_default_og_image_to_media_library(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(WebsiteSettings::class)
            ->set('data.brand.logo_upload', UploadedFile::fake()->image('logo.png', 320, 120))
            ->set('data.brand.favicon_upload', UploadedFile::fake()->image('favicon.png', 48, 48))
            ->set('data.seo.default_og_upload', UploadedFile::fake()->image('social.png', 1200, 630))
            ->call('save')
            ->assertHasNoErrors();

        foreach ([
            'brand.logo_media_id' => "Logo D'Mentai Indonesia",
            'brand.favicon_media_id' => "Favicon D'Mentai Indonesia",
            'seo.default_og_media_id' => "Default Open Graph image D'Mentai Indonesia",
        ] as $settingKey => $altText) {
            $mediaId = SiteSetting::query()->where('key', $settingKey)->value('value');
            $this->assertNotEmpty($mediaId);
            $media = Media::query()->findOrFail((int) $mediaId);
            $this->assertSame('brand', $media->folder);
            $this->assertSame('image/png', $media->mime_type);
            $this->assertSame($altText, $media->alt_text);
            Storage::disk('public')->assertExists($media->path);
        }

        $logoId = SiteSetting::query()->where('key', 'brand.logo_media_id')->value('value');
        $logo = Media::query()->findOrFail((int) $logoId);
        $faviconId = SiteSetting::query()->where('key', 'brand.favicon_media_id')->value('value');
        $favicon = Media::query()->findOrFail((int) $faviconId);

        $this->get('/')->assertOk()
            ->assertSee('Logo D&#039;Mentai Indonesia', false)
            ->assertSee('href="'.route('home').'" class="brand-lockup"', false)
            ->assertSee('src="'.$logo->url.'" alt="Logo D&#039;Mentai Indonesia" class="brand-logo"', false)
            ->assertSee('<link rel="icon" type="image/png" href="'.$favicon->url.'?v='.$favicon->id.'">', false);

        $defaultOgId = SiteSetting::query()->where('key', 'seo.default_og_media_id')->value('value');
        $defaultOg = Media::query()->findOrFail((int) $defaultOgId);
        $this->get('/menu')->assertSee('property="og:image" content="'.$defaultOg->url.'"', false);
        $this->assertSame(1, substr_count($this->get('/menu')->getContent(), 'property="og:image"'));
    }

    public function test_website_settings_can_clear_a_brand_asset_without_deleting_its_library_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(WebsiteSettings::class)
            ->set('data.brand.logo_upload', UploadedFile::fake()->image('logo.png', 320, 120))
            ->call('save')
            ->assertHasNoErrors();

        $media = Media::query()->where('folder', 'brand')->firstOrFail();
        $this->assertNotNull(SiteSetting::query()->where('key', 'brand.logo_media_id')->value('value'));

        Livewire::actingAs($admin)
            ->test(WebsiteSettings::class)
            ->set('data.brand.logo_upload', null)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(SiteSetting::query()->where('key', 'brand.logo_media_id')->value('value'));
        $this->assertDatabaseHas('media', ['id' => $media->id, 'folder' => 'brand']);
        Storage::disk('public')->assertExists($media->path);
        $this->get('/')->assertOk()->assertDontSee('class="brand-logo"', false)->assertSee("D'Mentai Indonesia");
    }

    public function test_brand_asset_upload_rejects_svg_and_unregistered_paths(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        Storage::disk('public')->put('brand-assets/logo/unsafe.svg', '<svg onload="alert(1)"/>');

        Livewire::actingAs($admin)
            ->test(WebsiteSettings::class)
            ->set('data.brand.logo_upload', UploadedFile::fake()->create('unsafe.svg', 1, 'image/svg+xml'))
            ->call('save')
            ->assertHasErrors(['data.brand.logo_upload']);

        try {
            app(BrandAssetManager::class)->registerUpload('brand-assets/logo/unsafe.svg', 'logo');
            $this->fail('SVG uploads should not be accepted as brand assets.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('data.brand.logo_upload', $exception->errors());
        }

        try {
            app(BrandAssetManager::class)->registerUpload('..\\.env', 'logo');
            $this->fail('Paths outside the brand upload directory should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('data.brand.logo_upload', $exception->errors());
        }

        $this->assertDatabaseCount('media', 0);
    }

    public function test_brand_asset_manager_accepts_a_valid_ico_favicon(): void
    {
        Storage::fake('public');
        $png = UploadedFile::fake()->image('icon-source.png', 8, 8)->get();
        $ico = "\x00\x00\x01\x00\x01\x00"."\x01\x01\x00\x00".pack('v', 1).pack('v', 32).pack('V', strlen($png)).pack('V', 22).$png;
        $path = 'brand-assets/favicon/favicon.ico';
        Storage::disk('public')->put($path, $ico);

        $media = app(BrandAssetManager::class)->registerUpload($path, 'favicon');

        $this->assertContains($media->mime_type, ['image/x-icon', 'image/vnd.microsoft.icon']);
        $this->assertSame('brand', $media->folder);
        Storage::disk('public')->assertExists($path);
    }

    public function test_website_settings_accepts_a_jpeg_favicon_and_uses_it_in_the_browser_tab(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(WebsiteSettings::class)
            ->set('data.brand.favicon_upload', UploadedFile::fake()->image('mascot.jpg', 64, 64))
            ->call('save')
            ->assertHasNoErrors();

        $faviconId = SiteSetting::query()->where('key', 'brand.favicon_media_id')->value('value');
        $favicon = Media::query()->findOrFail((int) $faviconId);

        $this->assertSame('image/jpeg', $favicon->mime_type);
        Storage::disk('public')->assertExists($favicon->path);
        $this->get('/')->assertOk()
            ->assertSee('<link rel="icon" type="image/jpeg" href="'.$favicon->url.'?v='.$favicon->id.'">', false);
    }

    public function test_media_upload_accepts_only_images_for_admins(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(ManageMedia::class)
            ->mountAction('create')
            ->setActionData([
                'path' => UploadedFile::fake()->image('brand.png', 80, 80),
                'folder' => 'brand',
                'alt_text' => 'Logo D’Mentai',
            ])
            ->callMountedAction()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('media', ['original_name' => 'brand.png', 'mime_type' => 'image/webp', 'folder' => 'brand']);
        $media = Media::query()->firstOrFail();
        $this->assertStringEndsWith('.webp', $media->path);
        Storage::disk('public')->assertExists($media->path);

        $storedPath = $media->path;
        $media->delete();
        Storage::disk('public')->assertMissing($storedPath);

        Livewire::actingAs($admin)
            ->test(ManageMedia::class)
            ->mountAction('create')
            ->setActionData(['path' => UploadedFile::fake()->create('shell.php', 20, 'application/x-php'), 'folder' => 'general'])
            ->callMountedAction()
            ->assertHasErrors(['mountedActions.0.data.path']);
    }

    public function test_pages_can_be_created_updated_and_duplicate_slugs_are_rejected(): void
    {
        $page = Page::query()->create(['title' => 'Tentang', 'slug' => 'Tentang Kami!', 'status' => 'draft', 'content' => '<p>Awal</p>']);
        $this->assertSame('tentang-kami', $page->slug);
        $page->update(['title' => 'Tentang Kami', 'excerpt' => 'Ringkasan']);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Tentang Kami', 'excerpt' => 'Ringkasan']);

        $this->expectException(QueryException::class);

        Page::query()->create(['title' => 'Duplikat', 'slug' => 'tentang-kami', 'status' => 'draft']);
    }

    public function test_admin_can_create_a_page_and_form_validation_rejects_duplicate_slugs(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Page::query()->create(['title' => 'Sudah ada', 'slug' => 'sudah-ada', 'status' => 'draft']);

        Livewire::actingAs($admin)
            ->test(ManagePages::class)
            ->mountAction('create')
            ->setActionData(['title' => 'Halaman Baru', 'slug' => 'halaman-baru', 'status' => 'draft', 'content' => '<p>Isi</p>'])
            ->callMountedAction()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pages', ['slug' => 'halaman-baru', 'title' => 'Halaman Baru']);

        Livewire::actingAs($admin)
            ->test(ManagePages::class)
            ->mountAction('create')
            ->setActionData(['title' => 'Duplikat', 'slug' => 'sudah-ada', 'status' => 'draft'])
            ->callMountedAction()
            ->assertHasActionErrors(['slug']);
    }

    public function test_only_published_pages_are_public_and_seo_overrides_and_fallbacks_work(): void
    {
        SiteSetting::query()->create(['group' => 'seo', 'key' => 'seo.default_title', 'value' => 'Judul Global']);
        SiteSetting::query()->create(['group' => 'seo', 'key' => 'seo.meta_description', 'value' => 'Deskripsi global']);
        Page::query()->create(['title' => 'Halaman Rahasia', 'slug' => 'rahasia', 'status' => 'draft']);
        $published = Page::query()->create(['title' => 'Tentang', 'slug' => 'tentang', 'excerpt' => 'Deskripsi halaman', 'content' => '<p>Isi aman</p>', 'status' => 'published']);

        $this->get('/rahasia')->assertNotFound();
        $this->get('/tentang')->assertOk()->assertSee('Tentang')->assertSee('Deskripsi halaman');

        $metadata = app(SeoManager::class)->forPage($published);
        $this->assertSame('Tentang', $metadata->title);
        $this->assertSame('Deskripsi halaman', $metadata->description);
        $this->assertSame(route('pages.show', 'tentang'), $metadata->canonical);

        $contentFallback = Page::query()->create([
            'title' => 'Informasi',
            'slug' => 'informasi',
            'content' => '<p>Konten utama dengan ringkasan SEO otomatis.</p>',
            'status' => 'published',
        ]);
        $this->assertSame('Konten utama dengan ringkasan SEO otomatis.', app(SeoManager::class)->forPage($contentFallback)->description);

        $globalFallback = Page::query()->create(['title' => 'Kosong', 'slug' => 'kosong', 'status' => 'published']);
        $this->assertSame('Deskripsi global', app(SeoManager::class)->forPage($globalFallback)->description);

        $published->update(['seo_title' => 'Judul manual', 'seo_description' => 'Deskripsi manual', 'canonical_url' => 'https://example.com/tentang']);
        $overridden = app(SeoManager::class)->forPage($published->fresh());
        $this->assertSame('Judul manual', $overridden->title);
        $this->assertSame('Deskripsi manual', $overridden->description);
        $this->assertSame('https://example.com/tentang', $overridden->canonical);
    }

    public function test_content_sanitizer_removes_scripts_inline_styles_and_javascript_links(): void
    {
        $safe = app(PageContentSanitizer::class)->sanitize('<p style="color:red" onclick="alert(1)">Aman</p><script>alert(1)</script><a href="javascript:alert(1)">link</a>');

        $this->assertStringContainsString('<p>Aman</p>', $safe);
        $this->assertStringNotContainsString('script', $safe);
        $this->assertStringNotContainsString('onclick', $safe);
        $this->assertStringNotContainsString('style=', $safe);
        $this->assertStringNotContainsString('javascript:', $safe);
    }

    public function test_sitemap_and_robots_only_advertise_public_routes(): void
    {
        Page::query()->create(['title' => 'Halaman Publik', 'slug' => 'publik', 'status' => 'published']);
        Page::query()->create(['title' => 'Halaman Draft', 'slug' => 'draft', 'status' => 'draft']);
        Page::query()->create(['title' => 'Halaman Noindex', 'slug' => 'noindex', 'status' => 'published', 'robots_directive' => 'noindex,follow']);

        $this->get('/sitemap.xml')->assertOk()->assertSee(route('home'))->assertSee(route('pages.show', 'publik'))->assertDontSee(route('pages.show', 'draft'));
        $this->get('/sitemap.xml')->assertDontSee(route('pages.show', 'noindex'));
        $robots = $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee(route('sitemap'));
        $this->assertStringNotContainsString("Disallow: /\n", $robots->getContent());
    }
}
