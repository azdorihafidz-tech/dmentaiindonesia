<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\PageView;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\GeoIpCountryLookup;
use App\Services\PageViewMetrics;
use App\Services\SiteSettings;
use App\Services\WhatsAppLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BusinessOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_shows_a_prefilled_whatsapp_order_button_only_when_contact_is_configured(): void
    {
        $category = ProductCategory::query()->create(['name' => 'Dimsum', 'slug' => 'dimsum', 'status' => 'active']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Gyoza Mentai',
            'slug' => 'gyoza-mentai',
            'status' => 'active',
        ]);

        $this->get(route('products.show', $product->slug))->assertOk()->assertDontSee('Pesan Gyoza Mentai via WhatsApp');

        app(SiteSettings::class)->save(['contact' => ['whatsapp' => '08123456789']]);
        $url = app(WhatsAppLink::class)->url('08123456789', "Halo D'Mentai, saya ingin memesan Gyoza Mentai.");

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Pesan Gyoza Mentai via WhatsApp')
            ->assertSee('href="'.e($url).'"', false);
    }

    public function test_outlet_list_has_a_real_maps_link_and_coordinates_can_supply_it(): void
    {
        Outlet::query()->create([
            'name' => 'Outlet Tautan',
            'slug' => 'outlet-tautan',
            'address' => 'Alamat outlet',
            'status' => 'active',
            'google_maps_url' => 'https://maps.app.goo.gl/example',
        ]);
        Outlet::query()->create([
            'name' => 'Outlet Koordinat',
            'slug' => 'outlet-koordinat',
            'status' => 'active',
            'latitude' => '-6.2000000',
            'longitude' => '106.8166667',
        ]);

        $this->get('/outlet')
            ->assertOk()
            ->assertSee('href="https://maps.app.goo.gl/example"', false)
            ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=-6.2000000%2C106.8166667', false);
    }

    public function test_public_page_views_are_recorded_and_unique_visitors_are_counted_from_sessions(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/menu')->assertOk();
        $this->assertDatabaseHas('page_views', ['path' => '/menu']);

        PageView::query()->create(['path' => '/promo', 'visitor_hash' => str_repeat('a', 64), 'viewed_at' => now()]);
        PageView::query()->create(['path' => '/outlet', 'visitor_hash' => str_repeat('a', 64), 'viewed_at' => now()]);
        PageView::query()->create(['path' => '/artikel', 'visitor_hash' => str_repeat('b', 64), 'viewed_at' => now()]);
        PageView::query()->create(['path' => '/menu-lama', 'visitor_hash' => str_repeat('c', 64), 'viewed_at' => now()->subDays(45)]);

        $this->assertSame(3, app(PageViewMetrics::class)->uniqueVisitorsSince(now()->startOfDay()));
        $this->withHeaders(['User-Agent' => 'Googlebot'])->get('/faq')->assertOk();
        $this->assertDatabaseMissing('page_views', ['path' => '/faq']);
    }

    public function test_local_and_private_network_requests_are_not_counted_as_public_visitors(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])->get('/menu')->assertOk();

        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_public_page_view_stores_only_the_country_resolved_from_the_request_ip(): void
    {
        $lookup = \Mockery::mock(GeoIpCountryLookup::class);
        $lookup->shouldReceive('resolve')->once()->andReturn(['country_code' => 'ID', 'country_name' => 'Indonesia']);
        $this->app->instance(GeoIpCountryLookup::class, $lookup);

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/menu')->assertOk();

        $this->assertDatabaseHas('page_views', [
            'path' => '/menu',
            'country_code' => 'ID',
            'country_name' => 'Indonesia',
        ]);
    }

    public function test_country_lookup_gracefully_skips_private_ips_and_missing_database(): void
    {
        config(['geoip.database_path' => storage_path('app/geoip/missing-test-database.mmdb')]);
        $lookup = new GeoIpCountryLookup;

        $this->assertNull($lookup->resolve('127.0.0.1'));
        $this->assertNull($lookup->resolve('192.168.1.20'));
        $this->assertNull($lookup->resolve('invalid-ip'));
        $this->assertNull($lookup->resolve('8.8.8.8'));
    }

    public function test_owner_dashboard_shows_real_stats_popular_pages_and_recent_activity(): void
    {
        $owner = User::factory()->create(['is_admin' => true]);
        PageView::query()->create(['path' => '/menu', 'visitor_hash' => str_repeat('d', 64), 'viewed_at' => now()]);
        ActivityLog::query()->create([
            'user_id' => $owner->id,
            'user_name' => $owner->name,
            'action' => 'updated',
            'subject_type' => 'Produk',
            'subject_name' => 'Gyoza Mentai',
            'created_at' => now(),
        ]);

        $this->actingAs($owner)->get('/admin')->assertOk()
            ->assertSee('Ringkasan website')
            ->assertSee('Pengunjung hari ini')
            ->assertSee('Halaman populer')
            ->assertSee('/menu')
            ->assertSee('Aktivitas admin terbaru')
            ->assertSee('Gyoza Mentai');
    }

    public function test_admin_can_review_recent_country_aggregates_without_exposing_session_identifiers(): void
    {
        $owner = User::factory()->create(['is_admin' => true]);
        PageView::query()->create([
            'path' => '/menu',
            'visitor_hash' => str_repeat('f', 64),
            'country_code' => 'ID',
            'country_name' => 'Indonesia',
            'viewed_at' => now(),
        ]);
        PageView::query()->create([
            'path' => '/promo',
            'visitor_hash' => str_repeat('f', 64),
            'country_code' => 'ID',
            'country_name' => 'Indonesia',
            'viewed_at' => now(),
        ]);
        PageView::query()->create([
            'path' => '/outlet',
            'visitor_hash' => str_repeat('a', 64),
            'country_code' => null,
            'country_name' => null,
            'viewed_at' => now(),
        ]);

        $this->actingAs($owner)->get('/admin/website-visitors')
            ->assertOk()
            ->assertSee('Statistik pengunjung')
            ->assertSee('Pengunjung per negara (30 hari)')
            ->assertSee('🇮🇩 Indonesia')
            ->assertSee('Pengunjung unik')
            ->assertSee('Belum diketahui')
            ->assertDontSee(str_repeat('f', 64));
    }

    public function test_homepage_displays_aggregate_visitors_and_country_flags(): void
    {
        PageView::query()->create([
            'path' => '/',
            'visitor_hash' => str_repeat('a', 64),
            'country_code' => 'ID',
            'country_name' => 'Indonesia',
            'viewed_at' => now(),
        ]);
        PageView::query()->create([
            'path' => '/menu',
            'visitor_hash' => str_repeat('b', 64),
            'country_code' => 'MY',
            'country_name' => 'Malaysia',
            'viewed_at' => now(),
        ]);

        $this->get('/')->assertOk()
            ->assertSee('visitor-stats-heading', false)
            ->assertSee('Page Views')
            ->assertSee('Visitors')
            ->assertSee('2 page views')
            ->assertSee('visitor-pageview-chart', false)
            ->assertSee('🇮🇩')
            ->assertSee('Indonesia')
            ->assertSee('🇲🇾')
            ->assertSee('Malaysia')
            ->assertSee('This product includes GeoLite Data created by');
    }

    public function test_owner_can_hide_public_visitor_statistics(): void
    {
        app(SiteSettings::class)->save(['analytics' => ['public_visitor_stats_enabled' => '0']]);

        $this->get('/')->assertOk()->assertDontSee('visitor-stats-heading', false);
    }

    public function test_editor_can_manage_content_but_only_owner_can_change_settings_or_users(): void
    {
        $owner = User::factory()->create(['is_admin' => true]);
        $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

        $this->actingAs($editor)->get('/admin/products')->assertOk();
        $this->actingAs($editor)->get('/admin/website-settings')->assertForbidden();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();

        $this->actingAs($owner)->get('/admin/website-settings')->assertOk();
        $this->actingAs($owner)->get('/admin/users')->assertOk();
    }

    public function test_last_owner_cannot_be_demoted_or_deleted(): void
    {
        $owner = User::factory()->create(['is_admin' => true]);

        try {
            $owner->update(['role' => 'editor']);
            $this->fail('The last owner must not be demoted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('role', $exception->errors());
        }

        try {
            $owner->delete();
            $this->fail('The last owner must not be deleted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('user', $exception->errors());
        }
    }
}
