<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads(): void
    {
        $this->get('/')->assertOk()->assertSee("D'Mentai Indonesia");
    }

    public function test_admin_login_page_is_available(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}
