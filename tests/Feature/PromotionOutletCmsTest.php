<?php

namespace Tests\Feature;

use App\Filament\Resources\Outlets\Pages\ManageOutlets;
use App\Filament\Resources\Promotions\Pages\ManagePromotions;
use App\Models\Outlet;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PromotionOutletCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotions_can_be_created_updated_and_slugs_are_unique(): void
    {
        $promotion = Promotion::query()->create(['title' => 'Promo Musiman', 'slug' => 'promo-musiman', 'status' => 'active']);
        $promotion->update(['short_description' => 'Deskripsi promo', 'sort_order' => 3]);
        $this->assertDatabaseHas('promotions', ['id' => $promotion->id, 'short_description' => 'Deskripsi promo', 'sort_order' => 3]);

        try {
            Promotion::query()->create(['title' => 'Duplikat', 'slug' => 'promo-musiman']);
            $this->fail('Unique promotion slugs must be enforced.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    public function test_promotions_reject_invalid_date_ranges_and_hide_inactive_or_expired_items(): void
    {
        try {
            Promotion::query()->create(['title' => 'Tanggal salah', 'slug' => 'tanggal-salah', 'start_at' => now(), 'end_at' => now()->subDay()]);
            $this->fail('Promotion end date cannot precede its start date.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('end_at', $exception->errors());
        }

        Promotion::query()->create(['title' => 'Sedang berlaku', 'slug' => 'sedang-berlaku', 'status' => 'active', 'short_description' => 'Informasi saat ini']);
        Promotion::query()->create(['title' => 'Kedaluwarsa', 'slug' => 'kedaluwarsa', 'status' => 'active', 'end_at' => now()->subDay()]);
        Promotion::query()->create(['title' => 'Nonaktif', 'slug' => 'nonaktif-promo', 'status' => 'inactive']);

        $this->get('/promo')->assertOk()->assertSee('Sedang berlaku')->assertDontSee('Kedaluwarsa')->assertDontSee('Nonaktif');
        $this->get('/promo/sedang-berlaku')->assertOk()->assertSee('Informasi saat ini');
        $this->get('/promo/kedaluwarsa')->assertNotFound();
    }

    public function test_admin_can_create_promotion_and_promotion_form_validates_dates(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)->test(ManagePromotions::class)
            ->mountAction('create')
            ->setActionData([
                'title' => 'Promo Admin', 'slug' => 'promo-admin', 'status' => 'active',
                'start_at' => now()->addDay()->toDateTimeString(), 'end_at' => now()->toDateTimeString(),
            ])
            ->callMountedAction()->assertHasActionErrors(['end_at']);

        Livewire::actingAs($admin)->test(ManagePromotions::class)
            ->mountAction('create')
            ->setActionData(['title' => 'Promo Admin', 'slug' => 'promo-admin', 'status' => 'active'])
            ->callMountedAction()->assertHasNoErrors();
    }

    public function test_outlets_can_be_created_updated_and_slugs_are_unique(): void
    {
        $outlet = Outlet::query()->create(['name' => 'Outlet Contoh Uji', 'slug' => 'outlet-uji', 'status' => 'active']);
        $outlet->update(['address' => 'Alamat untuk pengujian', 'sort_order' => 2]);
        $this->assertDatabaseHas('outlets', ['id' => $outlet->id, 'address' => 'Alamat untuk pengujian']);

        try {
            Outlet::query()->create(['name' => 'Duplikat', 'slug' => 'outlet-uji']);
            $this->fail('Unique outlet slugs must be enforced.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    public function test_outlet_url_and_coordinates_are_validated_and_only_active_outlets_are_public(): void
    {
        try {
            Outlet::query()->create(['name' => 'Koordinat salah', 'slug' => 'koordinat-salah', 'latitude' => 91]);
            $this->fail('Latitude must be within valid bounds.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('latitude', $exception->errors());
        }

        try {
            Outlet::query()->create(['name' => 'URL salah', 'slug' => 'url-salah', 'google_maps_url' => 'javascript:alert(1)']);
            $this->fail('Google Maps URL must be valid.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('google_maps_url', $exception->errors());
        }

        Outlet::query()->create(['name' => 'Outlet Terbuka', 'slug' => 'outlet-terbuka', 'status' => 'active', 'address' => 'Alamat dari data uji', 'google_maps_url' => 'https://maps.google.com/?q=test']);
        Outlet::query()->create(['name' => 'Outlet Tertutup', 'slug' => 'outlet-tertutup', 'status' => 'inactive']);

        $this->get('/outlet')->assertOk()->assertSee('Outlet Terbuka')->assertDontSee('Outlet Tertutup');
        $this->get('/outlet/outlet-terbuka')->assertOk()->assertSee('Alamat dari data uji')->assertSee('Lihat peta');
        $this->get('/outlet/outlet-tertutup')->assertNotFound();
    }

    public function test_admin_outlet_validation_and_all_phase_3b_resources_are_protected(): void
    {
        $this->get('/admin/promotions')->assertRedirect('/admin/login');
        $this->get('/admin/outlets')->assertRedirect('/admin/login');

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/promotions')->assertOk();
        $this->actingAs($admin)->get('/admin/outlets')->assertOk();

        Livewire::actingAs($admin)->test(ManageOutlets::class)
            ->mountAction('create')
            ->setActionData(['name' => 'Outlet Admin', 'slug' => 'outlet-admin', 'status' => 'active', 'latitude' => '91'])
            ->callMountedAction()->assertHasActionErrors(['latitude']);
    }

    public function test_sitemap_includes_only_available_promotions_and_outlets(): void
    {
        Promotion::query()->create(['title' => 'Promo Sitemap', 'slug' => 'promo-sitemap', 'status' => 'active']);
        Promotion::query()->create(['title' => 'Promo Tidak Aktif', 'slug' => 'promo-inactive', 'status' => 'inactive']);
        Outlet::query()->create(['name' => 'Outlet Sitemap', 'slug' => 'outlet-sitemap', 'status' => 'active']);
        Outlet::query()->create(['name' => 'Outlet Tidak Aktif', 'slug' => 'outlet-inactive', 'status' => 'inactive']);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee(route('promotions.show', 'promo-sitemap'))
            ->assertDontSee(route('promotions.show', 'promo-inactive'))
            ->assertSee(route('outlets.show', 'outlet-sitemap'))
            ->assertDontSee(route('outlets.show', 'outlet-inactive'));
    }
}
