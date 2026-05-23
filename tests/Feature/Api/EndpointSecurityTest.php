<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndpointSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ─── Cart Authorization Tests ──────────────────────────────────────

    public function test_customer_cannot_update_another_customers_cart_item(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $cart = Cart::factory()->create(['user_id' => $customer1->id]);
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($customer2)
            ->patch("/cart/{$cartItem->id}", ['quantity' => 3]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_delete_another_customers_cart_item(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $cart = Cart::factory()->create(['user_id' => $customer1->id]);
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($customer2)
            ->delete("/cart/{$cartItem->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_cart_index(): void
    {
        $response = $this->get('/cart');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_store_cart_item(): void
    {
        $response = $this->post('/cart', [
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_admin_cannot_access_cart_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/cart');

        $response->assertStatus(403);
    }

    public function test_delivery_man_cannot_access_cart_routes(): void
    {
        $delivery = User::factory()->delivery()->create();

        $response = $this->actingAs($delivery)->get('/cart');

        $response->assertStatus(403);
    }

    // ─── Order Authorization Tests ─────────────────────────────────────

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $order = Order::factory()->create(['user_id' => $customer1->id]);

        $response = $this->actingAs($customer2)
            ->get("/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_admin_order_routes(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/orders');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_place_order(): void
    {
        $response = $this->post('/orders', [
            'delivery_address' => '123 Test Street',
        ]);

        $response->assertRedirect('/login');
    }

    // ─── Admin Route Protection ────────────────────────────────────────

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_admin_stores(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/stores');

        $response->assertStatus(403);
    }

    public function test_delivery_man_cannot_access_admin_dashboard(): void
    {
        $delivery = User::factory()->delivery()->create();

        $response = $this->actingAs($delivery)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_delivery_man_cannot_access_admin_stores(): void
    {
        $delivery = User::factory()->delivery()->create();

        $response = $this->actingAs($delivery)->get('/admin/stores');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_admin_stores(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/stores');

        $response->assertStatus(200);
    }

    // ─── Rate Limiting Test ────────────────────────────────────────────

    public function test_customer_routes_are_rate_limited(): void
    {
        $customer = User::factory()->customer()->create();

        // Send 61 requests — the 61st should be rate limited
        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($customer)->get('/cart');
        }

        $response = $this->actingAs($customer)->get('/cart');

        $response->assertStatus(429);
    }

    // ─── Input Validation Tests ────────────────────────────────────────

    public function test_cart_store_rejects_invalid_product_id(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->post('/cart', [
            'product_id' => 99999,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_cart_store_rejects_zero_quantity(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($customer)->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_order_store_rejects_empty_delivery_address(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->post('/orders', [
            'delivery_address' => '',
        ]);

        $response->assertSessionHasErrors('delivery_address');
    }

    public function test_admin_product_store_rejects_missing_required_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/products', []);

        $response->assertSessionHasErrors(['name', 'store_id', 'category_id', 'type']);
    }
}
