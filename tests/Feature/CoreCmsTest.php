<?php

namespace Tests\Feature;

use App\Filament\Pages\WebsiteSettings;
use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Models\Media;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\PageContentSanitizer;
use App\Services\SeoManager;
use App\Services\SiteSettings;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CoreCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_can_be_saved_and_retrieved(): void
    {
        $settings = app(SiteSettings::class);
        $settings->save(['brand' => ['site_name' => "D'Mentai", 'tagline' => 'Dimsum']]);

        $this->assertSame("D'Mentai", app(SiteSettings::class)->get('brand.site_name'));
        $this->assertSame('Dimsum', SiteSetting::query()->where('key', 'brand.tagline')->value('value'));
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

        $this->assertDatabaseHas('media', ['original_name' => 'brand.png', 'mime_type' => 'image/png', 'folder' => 'brand']);
        $media = Media::query()->firstOrFail();
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
