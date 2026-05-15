<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── Registration Tests ────────────────────────────────────────────

    public function test_registration_page_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'email' => 'test@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_registration_defaults_to_customer_role(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('phone', '0912345678')->first();
        $this->assertEquals('customer', $user->role);
    }

    public function test_registration_can_assign_specific_role(): void
    {
        $this->post('/register', [
            'name' => 'Delivery User',
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'delivery',
        ]);

        $user = User::where('phone', '0912345678')->first();
        $this->assertEquals('delivery', $user->role);
    }

    public function test_registration_rejects_duplicate_phone(): void
    {
        User::factory()->create(['phone' => '0912345678']);

        $response = $this->post('/register', [
            'name' => 'Another User',
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertCount(1, User::where('phone', '0912345678')->get());
    }

    public function test_registration_requires_name(): void
    {
        $response = $this->post('/register', [
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_registration_requires_phone(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registration_email_is_optional(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'phone' => '0912345678',
            'email' => null,
        ]);
    }

    // ─── Login Tests ───────────────────────────────────────────────────

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'phone' => '0912345678',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'phone' => '0912345678',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_login_returns_generic_error_on_invalid_credentials(): void
    {
        User::factory()->create([
            'phone' => '0912345678',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'phone' => '0912345678',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('credentials');
        // Ensure no field-specific error hints
        $response->assertSessionDoesntHaveErrors(['phone', 'password']);
    }

    public function test_login_returns_generic_error_for_nonexistent_phone(): void
    {
        $response = $this->post('/login', [
            'phone' => '0999999999',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('credentials');
        // Ensure no field-specific error hints
        $response->assertSessionDoesntHaveErrors(['phone', 'password']);
    }

    // ─── Logout Tests ──────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
