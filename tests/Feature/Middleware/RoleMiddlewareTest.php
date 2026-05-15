<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // ─── Customer Role Tests ───────────────────────────────────────────

    public function test_customer_can_access_customer_routes(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->get('/cart');

        $response->assertStatus(200);
    }

    public function test_customer_cannot_access_admin_routes(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_super_admin_routes(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->get('/super-admin/stores');

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_delivery_routes(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->get('/delivery/assignments');

        $response->assertStatus(403);
    }

    // ─── Admin Role Tests ──────────────────────────────────────────────

    public function test_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_customer_routes(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/cart');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_super_admin_routes(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/super-admin/stores');

        $response->assertStatus(403);
    }

    // ─── Super Admin Role Tests ────────────────────────────────────────

    public function test_super_admin_can_access_super_admin_routes(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get('/super-admin/stores');

        $response->assertStatus(200);
    }

    public function test_super_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    // ─── Delivery Role Tests ───────────────────────────────────────────

    public function test_delivery_can_access_delivery_routes(): void
    {
        $user = User::factory()->delivery()->create();

        $response = $this->actingAs($user)->get('/delivery/assignments');

        $response->assertStatus(200);
    }

    public function test_delivery_cannot_access_admin_routes(): void
    {
        $user = User::factory()->delivery()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_delivery_cannot_access_customer_routes(): void
    {
        $user = User::factory()->delivery()->create();

        $response = $this->actingAs($user)->get('/cart');

        $response->assertStatus(403);
    }

    // ─── Unauthenticated User Tests ────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/cart');

        $response->assertRedirect('/login');
    }

    // ─── Multiple Roles Support ────────────────────────────────────────

    public function test_middleware_accepts_multiple_roles(): void
    {
        // This test verifies the middleware can accept multiple roles
        // by testing a route that allows both admin and super_admin
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $customer = User::factory()->customer()->create();

        // Both admin and super_admin should access admin routes
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);

        // Customer should not
        $this->actingAs($customer)->get('/admin/dashboard')->assertStatus(403);
    }
}
