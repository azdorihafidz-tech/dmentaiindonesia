<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductCategories\Pages\ManageProductCategories;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_can_be_created_updated_and_duplicate_slugs_are_rejected(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Dimsum', 'slug' => 'Dimsum!', 'status' => 'active']);
        $this->assertSame('dimsum', $category->slug);
        $category->update(['name' => 'Dimsum Pilihan', 'slug' => 'dimsum-pilihan', 'sort_order' => 2]);
        $this->assertDatabaseHas('product_categories', ['id' => $category->id, 'name' => 'Dimsum Pilihan', 'sort_order' => 2]);

        try {
            ProductCategory::query()->create(['name' => 'Duplikat', 'slug' => 'dimsum-pilihan']);
            $this->fail('Unique category slugs must be enforced.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    public function test_products_support_category_relationship_updates_and_unique_slugs(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Dimsum', 'slug' => 'dimsum', 'status' => 'active']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Dimsum Mentai',
            'slug' => 'dimsum-mentai',
            'price' => '25000.00',
            'promotional_price' => '20000.00',
            'status' => 'active',
        ]);

        $this->assertSame($category->id, $product->category->id);
        $product->update(['name' => 'Dimsum Mentai Spesial', 'short_description' => 'Deskripsi test']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Dimsum Mentai Spesial']);

        try {
            Product::query()->create(['category_id' => $category->id, 'name' => 'Duplikat', 'slug' => 'dimsum-mentai']);
            $this->fail('Unique product slugs must be enforced.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    public function test_invalid_prices_and_promotional_price_are_rejected_by_model_validation(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Dimsum', 'slug' => 'dimsum']);

        foreach ([
            ['price' => '-1.00'],
            ['price' => '10000.00', 'promotional_price' => '12000.00'],
        ] as $prices) {
            try {
                Product::query()->create($prices + ['category_id' => $category->id, 'name' => 'Invalid', 'slug' => 'invalid-'.count($prices)]);
                $this->fail('Invalid product pricing should fail validation.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_admin_can_create_categories_and_products_and_form_rejects_bad_prices(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)->test(ManageProductCategories::class)
            ->mountAction('create')
            ->setActionData(['name' => 'Gyoza', 'slug' => 'gyoza', 'status' => 'active', 'sort_order' => 0])
            ->callMountedAction()->assertHasNoErrors();

        $category = ProductCategory::query()->where('slug', 'gyoza')->firstOrFail();
        Livewire::actingAs($admin)->test(ManageProducts::class)
            ->mountAction('create')
            ->setActionData(['category_id' => $category->id, 'name' => 'Gyoza Original', 'slug' => 'gyoza-original', 'price' => '15000', 'promotional_price' => '18000', 'status' => 'active'])
            ->callMountedAction()->assertHasActionErrors(['promotional_price']);

        Livewire::actingAs($admin)->test(ManageProducts::class)
            ->mountAction('create')
            ->setActionData(['category_id' => $category->id, 'name' => 'Gyoza Original', 'slug' => 'gyoza-original', 'price' => '15000', 'status' => 'active'])
            ->callMountedAction()->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['slug' => 'gyoza-original', 'price' => '15000.00']);
    }

    public function test_public_menu_requires_active_product_and_active_category_and_uses_product_seo(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Gyoza', 'slug' => 'gyoza', 'status' => 'active']);
        ProductCategory::query()->create(['name' => 'Kosong', 'slug' => 'kategori-kosong', 'status' => 'active']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Gyoza Pedas',
            'slug' => 'gyoza-pedas',
            'short_description' => 'Deskripsi produk untuk SEO',
            'description' => '<p>Isi menu yang aman.</p>',
            'status' => 'active',
        ]);
        Product::query()->create(['category_id' => $category->id, 'name' => 'Nonaktif', 'slug' => 'nonaktif', 'status' => 'inactive']);

        $this->get('/menu')->assertOk()->assertSee('Gyoza Pedas')->assertDontSee('Nonaktif');
        $this->get('/menu/gyoza-pedas')->assertOk()->assertSee('Gyoza Pedas')->assertSee('Deskripsi produk untuk SEO')->assertSee('Gyoza Pedas |');
        $this->get('/menu/nonaktif')->assertNotFound();

        $inactiveCategory = ProductCategory::query()->create(['name' => 'Rahasia', 'slug' => 'rahasia', 'status' => 'inactive']);
        Product::query()->create(['category_id' => $inactiveCategory->id, 'name' => 'Tidak Tampil', 'slug' => 'tidak-tampil', 'status' => 'active']);
        $this->get('/menu/tidak-tampil')->assertNotFound();
    }

    public function test_product_admin_routes_require_admin_authorization(): void
    {
        $this->get('/admin/products')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/admin/product-categories')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/products')->assertOk();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/product-categories')->assertOk();
    }

    public function test_product_sitemap_includes_only_available_indexable_products(): void
    {
        $active = ProductCategory::query()->create(['name' => 'Gyoza', 'slug' => 'gyoza', 'status' => 'active']);
        Product::query()->create(['category_id' => $active->id, 'name' => 'Tampil', 'slug' => 'tampil', 'status' => 'active']);
        Product::query()->create(['category_id' => $active->id, 'name' => 'Noindex', 'slug' => 'noindex-produk', 'status' => 'active', 'robots_directive' => 'noindex,follow']);
        Product::query()->create(['category_id' => $active->id, 'name' => 'Nonaktif', 'slug' => 'nonaktif-produk', 'status' => 'inactive']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('products.show', 'tampil'))
            ->assertDontSee(route('products.show', 'noindex-produk'))
            ->assertDontSee(route('products.show', 'nonaktif-produk'));
    }
}
