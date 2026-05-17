<?php

namespace Tests\Feature\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\Customer\OrderService;
use App\Services\Delivery\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiStoreOrderGroupingTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;
    private DeliveryService $deliveryService;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
        $this->deliveryService = app(DeliveryService::class);
        $this->customer = User::factory()->customer()->create();
    }

    // ─── Multi-Store Order Grouping Tests ──────────────────────────────

    public function test_items_grouped_by_store_returns_correct_groups(): void
    {
        $store1 = Store::factory()->create(['name' => 'Store Alpha', 'address' => '123 Alpha St']);
        $store2 = Store::factory()->create(['name' => 'Store Beta', 'address' => '456 Beta Ave']);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'store_id' => $store1->id,
            'product_name' => 'Product A',
            'quantity' => 2,
            'price' => 10.00,
            'total' => 20.00,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'store_id' => $store1->id,
            'product_name' => 'Product B',
            'quantity' => 1,
            'price' => 15.00,
            'total' => 15.00,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'store_id' => $store2->id,
            'product_name' => 'Product C',
            'quantity' => 3,
            'price' => 5.00,
            'total' => 15.00,
        ]);

        $grouped = $order->getItemsGroupedByStore();

        $this->assertCount(2, $grouped);

        // Find store1 group
        $store1Group = $grouped->firstWhere('store_id', $store1->id);
        $this->assertNotNull($store1Group);
        $this->assertEquals('Store Alpha', $store1Group['store_name']);
        $this->assertEquals('123 Alpha St', $store1Group['store_address']);
        $this->assertCount(2, $store1Group['items']);

        // Find store2 group
        $store2Group = $grouped->firstWhere('store_id', $store2->id);
        $this->assertNotNull($store2Group);
        $this->assertEquals('Store Beta', $store2Group['store_name']);
        $this->assertEquals('456 Beta Ave', $store2Group['store_address']);
        $this->assertCount(1, $store2Group['items']);
    }

    public function test_items_grouped_by_store_each_item_in_correct_group(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();
        $store3 = Store::factory()->create();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $item1 = OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store1->id, 'product_name' => 'Item 1']);
        $item2 = OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store2->id, 'product_name' => 'Item 2']);
        $item3 = OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store3->id, 'product_name' => 'Item 3']);
        $item4 = OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store1->id, 'product_name' => 'Item 4']);

        $grouped = $order->getItemsGroupedByStore();

        $this->assertCount(3, $grouped);

        // Verify each item is in the correct group
        $store1Group = $grouped->firstWhere('store_id', $store1->id);
        $store1ItemNames = $store1Group['items']->pluck('product_name')->toArray();
        $this->assertContains('Item 1', $store1ItemNames);
        $this->assertContains('Item 4', $store1ItemNames);

        $store2Group = $grouped->firstWhere('store_id', $store2->id);
        $store2ItemNames = $store2Group['items']->pluck('product_name')->toArray();
        $this->assertContains('Item 2', $store2ItemNames);

        $store3Group = $grouped->firstWhere('store_id', $store3->id);
        $store3ItemNames = $store3Group['items']->pluck('product_name')->toArray();
        $this->assertContains('Item 3', $store3ItemNames);
    }

    public function test_items_grouped_by_store_includes_store_addresses(): void
    {
        $store1 = Store::factory()->create(['address' => '100 Main Street', 'phone' => '0911111111']);
        $store2 = Store::factory()->create(['address' => '200 Oak Avenue', 'phone' => '0922222222']);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store1->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store2->id]);

        $grouped = $order->getItemsGroupedByStore();

        $addresses = $grouped->pluck('store_address')->toArray();
        $this->assertContains('100 Main Street', $addresses);
        $this->assertContains('200 Oak Avenue', $addresses);

        $phones = $grouped->pluck('store_phone')->toArray();
        $this->assertContains('0911111111', $phones);
        $this->assertContains('0922222222', $phones);
    }

    public function test_single_store_order_returns_one_group(): void
    {
        $store = Store::factory()->create();
        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        OrderItem::factory()->count(3)->create([
            'order_id' => $order->id,
            'store_id' => $store->id,
        ]);

        $grouped = $order->getItemsGroupedByStore();

        $this->assertCount(1, $grouped);
        $this->assertCount(3, $grouped->first()['items']);
    }

    public function test_get_order_loads_store_relationship(): void
    {
        $store = Store::factory()->create();
        $order = Order::factory()->create(['user_id' => $this->customer->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store->id]);

        $result = $this->orderService->getOrder($order);

        $this->assertTrue($result->relationLoaded('items'));
        // Verify store is loaded on items
        $this->assertTrue($result->items->first()->relationLoaded('store'));
    }

    public function test_delivery_service_loads_store_addresses(): void
    {
        $store1 = Store::factory()->create(['address' => 'Pickup Address 1']);
        $store2 = Store::factory()->create(['address' => 'Pickup Address 2']);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store1->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'store_id' => $store2->id]);

        $deliveryMan = User::factory()->delivery()->create();
        $admin = User::factory()->admin()->create();

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'delivery_man_id' => $deliveryMan->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $result = $this->deliveryService->getDelivery($delivery);

        // Verify store is loaded on order items
        $this->assertTrue($result->relationLoaded('order'));
        $this->assertTrue($result->order->relationLoaded('items'));
        $this->assertTrue($result->order->items->first()->relationLoaded('store'));

        // Verify store addresses are accessible
        $storeAddresses = $result->order->items->pluck('store.address')->unique()->toArray();
        $this->assertContains('Pickup Address 1', $storeAddresses);
        $this->assertContains('Pickup Address 2', $storeAddresses);
    }

    public function test_multi_store_order_has_single_delivery_fee(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();
        $store3 = Store::factory()->create();

        $product1 = Product::factory()->create(['store_id' => $store1->id, 'price' => 10.00, 'is_available' => true]);
        $product2 = Product::factory()->create(['store_id' => $store2->id, 'price' => 20.00, 'is_available' => true]);
        $product3 = Product::factory()->create(['store_id' => $store3->id, 'price' => 30.00, 'is_available' => true]);

        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product1->id, 'quantity' => 1, 'price' => 10.00]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product2->id, 'quantity' => 1, 'price' => 20.00]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product3->id, 'quantity' => 1, 'price' => 30.00]);

        $order = $this->orderService->placeOrder($this->customer, [
            'delivery_address' => '789 Customer St',
            'latitude' => 33.52,
            'longitude' => 36.30,
        ]);

        // Verify single delivery fee range (not per-store)
        $this->assertNotNull($order->delivery_fee_min);
        $this->assertNotNull($order->delivery_fee_max);
        // Total should be subtotal + single fee_max, not fee_max * store_count
        $expectedTotal = (float) $order->subtotal + (float) $order->delivery_fee_max;
        $this->assertEquals($expectedTotal, (float) $order->total);

        // Verify items from all 3 stores are in the order
        $storeIds = $order->items->pluck('store_id')->unique();
        $this->assertCount(3, $storeIds);
    }

    public function test_multi_store_order_has_single_order_number(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        $product1 = Product::factory()->create(['store_id' => $store1->id, 'price' => 15.00, 'is_available' => true]);
        $product2 = Product::factory()->create(['store_id' => $store2->id, 'price' => 25.00, 'is_available' => true]);

        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product1->id, 'quantity' => 1, 'price' => 15.00]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product2->id, 'quantity' => 1, 'price' => 25.00]);

        $order = $this->orderService->placeOrder($this->customer, [
            'delivery_address' => '789 Customer St',
            'latitude' => 33.52,
            'longitude' => 36.30,
        ]);

        // Single order number for multi-store order
        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);

        // All items belong to the same order
        $this->assertTrue($order->items->every(fn ($item) => $item->order_id === $order->id));
    }
}
