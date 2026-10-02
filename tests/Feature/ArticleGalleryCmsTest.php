<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleCategories\Pages\ManageArticleCategories;
use App\Filament\Resources\Articles\Pages\ManageArticles;
use App\Filament\Resources\Gallery\Pages\ManageGallery;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleGalleryCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_categories_support_crud_and_unique_slugs(): void
    {
        $category = ArticleCategory::query()->create(['name' => 'Tips', 'slug' => 'Tips!', 'status' => 'active']);
        $this->assertSame('tips', $category->slug);
        $category->update(['name' => 'Tips Dimsum', 'slug' => 'tips-dimsum', 'sort_order' => 4]);
        $this->assertDatabaseHas('article_categories', ['id' => $category->id, 'name' => 'Tips Dimsum', 'sort_order' => 4]);

        try {
            ArticleCategory::query()->create(['name' => 'Duplikat', 'slug' => 'tips-dimsum']);
            $this->fail('Unique article category slugs must be enforced.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    public function test_articles_support_crud_relationship_and_unique_slugs(): void
    {
        $category = ArticleCategory::query()->create(['name' => 'Tips', 'slug' => 'tips']);
        $article = Article::query()->create([
            'category_id' => $category->id, 'title' => 'Cara Menikmati Dimsum', 'slug' => 'cara-menikmati-dimsum',
            'excerpt' => 'Ringkasan artikel', 'content' => '<p>Isi artikel.</p>', 'status' => 'draft',
        ]);
        $this->assertSame($category->id, $article->category->id);
        $article->update(['title' => 'Cara Menikmati Dimsum Bersama', 'excerpt' => 'Ringkasan baru']);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => 'Cara Menikmati Dimsum Bersama']);

        try {
            Article::query()->create(['title' => 'Duplikat', 'slug' => 'cara-menikmati-dimsum']);
            $this->fail('Unique article slugs must be enforced.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    public function test_published_articles_require_valid_publish_time_and_public_seo_uses_phase_2_service(): void
    {
        $category = ArticleCategory::query()->create(['name' => 'Tips', 'slug' => 'tips', 'status' => 'active']);
        $draft = Article::query()->create(['category_id' => $category->id, 'title' => 'Draft', 'slug' => 'draft-artikel', 'status' => 'draft']);
        $published = Article::query()->create([
            'category_id' => $category->id, 'title' => 'Artikel Terbit', 'slug' => 'artikel-terbit',
            'excerpt' => 'Deskripsi artikel', 'content' => '<h2>Isi aman</h2><script>alert(1)</script>', 'status' => 'published',
        ]);
        $scheduled = Article::query()->create([
            'title' => 'Artikel Terjadwal', 'slug' => 'artikel-terjadwal', 'status' => 'published', 'published_at' => now()->addDay(),
        ]);

        $this->assertNotNull($published->published_at);
        $this->assertFalse(Article::published()->whereKey($draft)->exists());
        $this->assertFalse(Article::published()->whereKey($scheduled)->exists());
        $this->assertTrue(Article::published()->whereKey($published)->exists());

        $this->get('/artikel')->assertOk()->assertSee('Artikel Terbit')->assertDontSee('Draft')->assertDontSee('Artikel Terjadwal');
        $this->get('/artikel/artikel-terbit')->assertOk()->assertSee('Deskripsi artikel')->assertSee('Artikel Terbit |')->assertDontSee('<script>');
        $this->get('/artikel/draft-artikel')->assertNotFound();
        $this->get('/artikel/artikel-terjadwal')->assertNotFound();

        $published->update(['seo_title' => 'Judul Artikel Manual', 'seo_description' => 'Deskripsi manual']);
        $this->get('/artikel/artikel-terbit')->assertSee('<title>Judul Artikel Manual</title>', false)->assertSee('name="description" content="Deskripsi manual"', false);
        $this->get('/artikel?kategori=tips')->assertOk()->assertSee('Artikel Terbit');
    }

    public function test_admin_can_create_article_category_and_article_and_requires_published_at_consistently(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Livewire::actingAs($admin)->test(ManageArticleCategories::class)
            ->mountAction('create')
            ->setActionData(['name' => 'Panduan', 'slug' => 'panduan', 'status' => 'active'])
            ->callMountedAction()->assertHasNoErrors();

        $category = ArticleCategory::query()->where('slug', 'panduan')->firstOrFail();
        Livewire::actingAs($admin)->test(ManageArticles::class)
            ->mountAction('create')
            ->setActionData(['category_id' => $category->id, 'title' => 'Artikel Admin', 'slug' => 'artikel-admin', 'status' => 'published'])
            ->callMountedAction()->assertHasNoErrors();

        $this->assertDatabaseHas('articles', ['slug' => 'artikel-admin', 'status' => 'published']);
        $this->assertNotNull(Article::query()->where('slug', 'artikel-admin')->firstOrFail()->published_at);

        Livewire::actingAs($admin)->test(ManageArticles::class)
            ->mountAction('create')
            ->setActionData(['title' => 'Duplikat Admin', 'slug' => 'artikel-admin', 'status' => 'draft'])
            ->callMountedAction()->assertHasActionErrors(['slug']);
    }

    public function test_gallery_items_require_media_and_only_active_media_items_are_public_in_order(): void
    {
        Storage::fake('public');
        $media = Media::query()->create([
            'disk' => 'public', 'path' => 'gallery/photo.jpg', 'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg', 'file_size' => 100, 'alt_text' => 'Foto uji',
        ]);
        Storage::disk('public')->put($media->path, 'test');

        $later = GalleryItem::query()->create(['title' => 'Foto kedua', 'image_media_id' => $media->id, 'status' => 'active', 'sort_order' => 2]);
        GalleryItem::query()->create(['title' => 'Foto pertama', 'image_media_id' => $media->id, 'status' => 'active', 'sort_order' => 1]);
        GalleryItem::query()->create(['title' => 'Foto privat', 'image_media_id' => $media->id, 'status' => 'inactive', 'sort_order' => 0]);
        GalleryItem::query()->create(['title' => 'Foto tanpa media', 'status' => 'active', 'sort_order' => 3]);
        $later->update(['caption' => 'Keterangan diubah']);
        $this->assertDatabaseHas('gallery_items', ['id' => $later->id, 'caption' => 'Keterangan diubah']);

        $this->get('/galeri')->assertOk()->assertSeeInOrder(['Foto pertama', 'Keterangan diubah'])
            ->assertSee('alt="Foto uji"', false)->assertSee('loading="lazy"', false)
            ->assertDontSee('Foto privat')->assertDontSee('Foto tanpa media');
    }

    public function test_gallery_admin_requires_authorization_and_valid_image_selection(): void
    {
        $this->get('/admin/articles')->assertRedirect('/admin/login');
        $this->get('/admin/article-categories')->assertRedirect('/admin/login');
        $this->get('/admin/gallery')->assertRedirect('/admin/login');

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/articles')->assertOk();
        $this->actingAs($admin)->get('/admin/article-categories')->assertOk();
        $this->actingAs($admin)->get('/admin/gallery')->assertOk();

        Livewire::actingAs($admin)->test(ManageGallery::class)
            ->mountAction('create')
            ->setActionData(['title' => 'Foto tanpa gambar', 'status' => 'active'])
            ->callMountedAction()->assertHasActionErrors(['image_media_id']);
    }

    public function test_sitemap_includes_only_published_indexable_articles(): void
    {
        Article::query()->create(['title' => 'Artikel Sitemap', 'slug' => 'artikel-sitemap', 'status' => 'published']);
        Article::query()->create(['title' => 'Artikel Draft Sitemap', 'slug' => 'artikel-draft-sitemap', 'status' => 'draft']);
        Article::query()->create(['title' => 'Artikel Noindex Sitemap', 'slug' => 'artikel-noindex-sitemap', 'status' => 'published', 'robots_directive' => 'noindex,follow']);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee(route('articles.show', 'artikel-sitemap'))
            ->assertDontSee(route('articles.show', 'artikel-draft-sitemap'))
            ->assertDontSee(route('articles.show', 'artikel-noindex-sitemap'));
    }
}
