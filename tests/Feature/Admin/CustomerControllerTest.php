<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    // ─── Index Tests ────────────────────────────────────────────────────

    public function test_index_lists_customers(): void
    {
        User::factory()->count(3)->create(['role' => 'customer']);

        $this->actingAs($this->admin)
            ->get('/admin/customers')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Customers/Index')
                ->has('customers.data', 3)
            );
    }

    public function test_index_excludes_non_customers(): void
    {
        User::factory()->create(['role' => 'customer']);
        User::factory()->admin()->create();
        User::factory()->delivery()->create();

        $this->actingAs($this->admin)
            ->get('/admin/customers')
            ->assertInertia(fn ($page) => $page
                ->has('customers.data', 1)
            );
    }

    public function test_index_filters_by_search(): void
    {
        User::factory()->create(['role' => 'customer', 'name' => 'أحمد محمد']);
        User::factory()->create(['role' => 'customer', 'name' => 'علي حسن']);

        $this->actingAs($this->admin)
            ->get('/admin/customers?search=أحمد')
            ->assertInertia(fn ($page) => $page
                ->has('customers.data', 1)
            );
    }

    public function test_index_excludes_soft_deleted_customers(): void
    {
        User::factory()->create(['role' => 'customer']);
        User::factory()->create(['role' => 'customer'])->delete();

        $this->actingAs($this->admin)
            ->get('/admin/customers')
            ->assertInertia(fn ($page) => $page
                ->has('customers.data', 1)
            );
    }

    // ─── Show Tests ─────────────────────────────────────────────────────

    public function test_show_displays_customer_details(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($this->admin)
            ->get("/admin/customers/{$customer->id}")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Customers/Show')
                ->where('customer.id', $customer->id)
                ->has('orders')
            );
    }

    public function test_show_shows_customer_orders(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        Order::factory()->count(2)->create(['user_id' => $customer->id]);

        $this->actingAs($this->admin)
            ->get("/admin/customers/{$customer->id}")
            ->assertInertia(fn ($page) => $page
                ->has('orders.data', 2)
            );
    }

    // ─── Destroy Tests ──────────────────────────────────────────────────

    public function test_destroy_soft_deletes_customer(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($this->admin)
            ->delete("/admin/customers/{$customer->id}")
            ->assertRedirect('/admin/customers');

        $this->assertSoftDeleted($customer);
    }

    // ─── Trash Tests ────────────────────────────────────────────────────

    public function test_trash_lists_soft_deleted_customers(): void
    {
        User::factory()->create(['role' => 'customer'])->delete();

        $this->actingAs($this->admin)
            ->get('/admin/customers/trash')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Customers/Trash')
                ->has('customers.data', 1)
            );
    }

    // ─── Restore Tests ──────────────────────────────────────────────────

    public function test_restore_recovers_soft_deleted_customer(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->delete();

        $this->actingAs($this->admin)
            ->patch("/admin/customers/{$customer->id}/restore")
            ->assertRedirect('/admin/customers/trash');

        $this->assertNotSoftDeleted($customer);
    }

    // ─── Force Destroy Tests ────────────────────────────────────────────

    public function test_force_destroy_permanently_deletes_customer(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->delete();

        $this->actingAs($this->admin)
            ->delete("/admin/customers/{$customer->id}/force")
            ->assertRedirect('/admin/customers/trash');

        $this->assertModelMissing($customer);
    }

    public function test_force_destroy_deletes_customer_orders(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $customer->id]);
        $customer->delete();

        $this->actingAs($this->admin)
            ->delete("/admin/customers/{$customer->id}/force");

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_force_destroy_deletes_customer_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $cart = \App\Models\Cart::factory()->create(['user_id' => $customer->id]);
        $customer->delete();

        $this->actingAs($this->admin)
            ->delete("/admin/customers/{$customer->id}/force");

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    // ─── Authorization Tests ────────────────────────────────────────────

    public function test_non_admin_cannot_access_customers(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get('/admin/customers')
            ->assertStatus(403);
    }
}
