<?php

namespace Tests\Feature\Services;

use App\Exceptions\ProductUnavailableException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderService::class);
        $this->customer = User::factory()->customer()->create();
    }

    // ─── placeOrder Tests ──────────────────────────────────────────────

    public function test_place_order_creates_order_with_pending_status(): void
    {
        $this->seedCartWithItems();

        $order = $this->service->placeOrder($this->customer, $this->orderData());

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals($this->customer->id, $order->user_id);
    }

    public function test_place_order_generates_unique_order_number(): void
    {
        $this->seedCartWithItems();

        $order = $this->service->placeOrder($this->customer, $this->orderData());

        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
        $this->assertEquals(12, strlen($order->order_number)); // ORD- + 8 chars
    }

    public function test_place_order_creates_order_items_from_cart_items(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'price' => 25.00,
            'is_available' => true,
        ]);

        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 3,
            'price' => 75.00, // 25 * 3
        ]);

        $order = $this->service->placeOrder($this->customer, $this->orderData());

        $this->assertCount(1, $order->items);
        $orderItem = $order->items->first();
        $this->assertEquals($store->id, $orderItem->store_id);
        $this->assertEquals($product->id, $orderItem->product_id);
        $this->assertNull($orderItem->variant_id);
        $this->assertEquals($product->name, $orderItem->product_name);
        $this->assertEquals(3, $orderItem->quantity);
        $this->assertEquals(25.00, (float) $orderItem->price);
        $this->assertEquals(75.00, (float) $orderItem->total);
    }

    public function test_place_order_creates_order_items_with_variant(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'price' => 20.00,
            'is_available' => true,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 30.00,
        ]);

        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 60.00, // 30 * 2
        ]);

        $order = $this->service->placeOrder($this->customer, $this->orderData());

        $orderItem = $order->items->first();
        $this->assertEquals($variant->id, $orderItem->variant_id);
        $this->assertEquals(30.00, (float) $orderItem->price);
        $this->assertEquals(60.00, (float) $orderItem->total);
    }

    public function test_place_order_calculates_subtotal_correctly(): void
    {
        $store = Store::factory()->create();
        $product1 = Product::factory()->create(['store_id' => $store->id, 'price' => 10.00, 'is_available' => true]);
        $product2 = Product::factory()->create(['store_id' => $store->id, 'price' => 20.00, 'is_available' => true]);

        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 20.00,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 20.00,
        ]);

        $order = $this->service->placeOrder($this->customer, $this->orderData());

        // subtotal = sum of cart item prices = 20 + 20 = 40
        $this->assertEquals(40.00, (float) $order->subtotal);
    }

    public function test_place_order_sets_delivery_fee_range(): void
    {
        $this->seedCartWithItems();

        $order = $this->service->placeOrder($this->customer, $this->orderData());

        $this->assertNotNull($order->delivery_fee_min);
        $this->assertNotNull($order->delivery_fee_max);
        $this->assertGreaterThan(0, (float) $order->delivery_fee_min);
        $this->assertGreaterThanOrEqual((float) $order->delivery_fee_min, (float) $order->delivery_fee_max);
        $this->assertNull($order->delivery_fee); // No actual fee set for pending orders
    }

    public function test_place_order_calculates_total_as_subtotal_plus_fee_max(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 100.00, 'is_available' => true]);

        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100.00,
        ]);

        $order = $this->service->placeOrder($this->customer, $this->orderData());

        $expectedTotal = (float) $order->subtotal + (float) $order->delivery_fee_max;
        $this->assertEquals($expectedTotal, (float) $order->total);
    }

    public function test_place_order_clears_cart_after_creation(): void
    {
        $this->seedCartWithItems();

        $this->service->placeOrder($this->customer, $this->orderData());

        $cart = Cart::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($cart);
        $this->assertCount(0, $cart->items);
    }

    public function test_place_order_stores_delivery_address_and_coordinates(): void
    {
        $this->seedCartWithItems();

        $data = [
            'delivery_address' => '123 Test Street, Damascus',
            'latitude' => 33.5138,
            'longitude' => 36.2765,
            'notes' => 'Ring the bell twice',
        ];

        $order = $this->service->placeOrder($this->customer, $data);

        $this->assertEquals('123 Test Street, Damascus', $order->delivery_address);
        $this->assertEquals(33.5138, (float) $order->latitude);
        $this->assertEquals(36.2765, (float) $order->longitude);
        $this->assertEquals('Ring the bell twice', $order->notes);
    }

    public function test_place_order_handles_optional_notes(): void
    {
        $this->seedCartWithItems();

        $data = [
            'delivery_address' => '123 Test Street',
            'latitude' => 33.5138,
            'longitude' => 36.2765,
        ];

        $order = $this->service->placeOrder($this->customer, $data);

        $this->assertNull($order->notes);
    }

    // ─── Rejection Tests ───────────────────────────────────────────────

    public function test_place_order_rejects_empty_cart(): void
    {
        // Create cart with no items
        Cart::factory()->create(['user_id' => $this->customer->id]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot place an order with an empty cart.');

        $this->service->placeOrder($this->customer, $this->orderData());
    }

    public function test_place_order_rejects_unavailable_products(): void
    {
        $product = Product::factory()->create(['is_available' => true]);
        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10.00,
        ]);

        // Mark product as unavailable after adding to cart
        $product->update(['is_available' => false]);

        $this->expectException(ProductUnavailableException::class);

        $this->service->placeOrder($this->customer, $this->orderData());
    }

    public function test_place_order_identifies_multiple_unavailable_products(): void
    {
        $product1 = Product::factory()->create(['name' => 'Product A', 'is_available' => true]);
        $product2 = Product::factory()->create(['name' => 'Product B', 'is_available' => true]);
        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product1->id, 'quantity' => 1, 'price' => 10.00]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product2->id, 'quantity' => 1, 'price' => 20.00]);

        // Mark both as unavailable
        $product1->update(['is_available' => false]);
        $product2->update(['is_available' => false]);

        try {
            $this->service->placeOrder($this->customer, $this->orderData());
            $this->fail('Expected ProductUnavailableException was not thrown.');
        } catch (ProductUnavailableException $e) {
            $unavailable = $e->getUnavailableProducts();
            $this->assertCount(2, $unavailable);
            $this->assertContains('Product A', $unavailable);
            $this->assertContains('Product B', $unavailable);
        }
    }

    // ─── Multi-Store Order Tests ───────────────────────────────────────

    public function test_place_order_supports_items_from_multiple_stores(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();
        $product1 = Product::factory()->create(['store_id' => $store1->id, 'price' => 15.00, 'is_available' => true]);
        $product2 = Product::factory()->create(['store_id' => $store2->id, 'price' => 25.00, 'is_available' => true]);

        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product1->id, 'quantity' => 1, 'price' => 15.00]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product2->id, 'quantity' => 1, 'price' => 25.00]);

        $order = $this->service->placeOrder($this->customer, $this->orderData());

        $this->assertCount(2, $order->items);
        $storeIds = $order->items->pluck('store_id')->unique()->values()->toArray();
        $this->assertCount(2, $storeIds);
        $this->assertContains($store1->id, $storeIds);
        $this->assertContains($store2->id, $storeIds);
    }

    // ─── Transaction Tests ─────────────────────────────────────────────

    public function test_place_order_does_not_clear_cart_on_failure(): void
    {
        $product = Product::factory()->create(['is_available' => true]);
        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 10.00]);

        // Mark product unavailable to trigger exception
        $product->update(['is_available' => false]);

        try {
            $this->service->placeOrder($this->customer, $this->orderData());
        } catch (ProductUnavailableException $e) {
            // Cart should still have items since the exception is thrown before the transaction
            $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id]);
        }
    }

    // ─── getOrders Tests ───────────────────────────────────────────────

    public function test_get_orders_returns_paginated_results(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->customer->id]);

        $request = new \Illuminate\Http\Request();
        $result = $this->service->getOrders($this->customer, $request);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertCount(3, $result->items());
    }

    public function test_get_orders_only_returns_user_orders(): void
    {
        $otherCustomer = User::factory()->customer()->create();
        Order::factory()->count(2)->create(['user_id' => $this->customer->id]);
        Order::factory()->count(3)->create(['user_id' => $otherCustomer->id]);

        $request = new \Illuminate\Http\Request();
        $result = $this->service->getOrders($this->customer, $request);

        $this->assertCount(2, $result->items());
    }

    // ─── getOrder Tests ────────────────────────────────────────────────

    public function test_get_order_loads_items_and_delivery(): void
    {
        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $result = $this->service->getOrder($order);

        $this->assertTrue($result->relationLoaded('items'));
        $this->assertTrue($result->relationLoaded('delivery'));
    }

    // ─── Helpers ───────────────────────────────────────────────────────

    private function seedCartWithItems(int $count = 2): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);

        for ($i = 0; $i < $count; $i++) {
            $product = Product::factory()->create(['price' => 10.00 + $i * 5, 'is_available' => true]);
            CartItem::factory()->create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => 10.00 + $i * 5,
            ]);
        }
    }

    private function orderData(array $overrides = []): array
    {
        return array_merge([
            'delivery_address' => '123 Test Street, Damascus',
            'latitude' => 33.52,
            'longitude' => 36.30,
            'notes' => null,
        ], $overrides);
    }
}
