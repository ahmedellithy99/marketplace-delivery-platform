<?php

namespace Tests\Feature\Services;

use App\Exceptions\ProductUnavailableException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use App\Services\Customer\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $service;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CartService::class);
        $this->customer = User::factory()->customer()->create();
    }

    // ─── getOrCreateCart Tests ──────────────────────────────────────────

    public function test_get_or_create_cart_creates_cart_for_new_user(): void
    {
        $cart = $this->service->getOrCreateCart($this->customer);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals($this->customer->id, $cart->user_id);
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer->id,
        ]);
    }

    public function test_get_or_create_cart_returns_existing_cart(): void
    {
        $existingCart = Cart::factory()->create(['user_id' => $this->customer->id]);

        $cart = $this->service->getOrCreateCart($this->customer);

        $this->assertEquals($existingCart->id, $cart->id);
        $this->assertDatabaseCount('carts', 1);
    }

    // ─── getCart Tests ──────────────────────────────────────────────────

    public function test_get_cart_returns_cart_with_items_loaded(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->create(['cart_id' => $cart->id]);

        $result = $this->service->getCart($this->customer);

        $this->assertTrue($result->relationLoaded('items'));
        $this->assertCount(1, $result->items);
    }

    // ─── addCartItem Tests ─────────────────────────────────────────────

    public function test_add_cart_item_with_base_price(): void
    {
        $product = Product::factory()->create(['base_price' => 20.00]);

        $cartItem = $this->service->addCartItem($this->customer, $product->id, null, 2);

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals($product->id, $cartItem->product_id);
        $this->assertNull($cartItem->variant_id);
        $this->assertEquals(2, (float) $cartItem->quantity);
        $this->assertEquals(20.00, (float) $cartItem->unit_price);
        $this->assertEquals(40.00, (float) $cartItem->total_price);
    }

    public function test_add_cart_item_with_discounted_price(): void
    {
        $product = Product::factory()->create(['base_price' => 30.00]);

        $cartItem = $this->service->addCartItem($this->customer, $product->id, null, 3);

        $this->assertEquals(30.00, (float) $cartItem->unit_price);
        $this->assertEquals(90.00, (float) $cartItem->total_price);
    }

    public function test_add_cart_item_with_variant_price_overrides_discount(): void
    {
        $product = Product::factory()->variant()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 35.00,
        ]);

        $cartItem = $this->service->addCartItem($this->customer, $product->id, $variant->id, 2);

        $this->assertEquals($variant->id, $cartItem->variant_id);
        $this->assertEquals(35.00, (float) $cartItem->unit_price);
        $this->assertEquals(70.00, (float) $cartItem->total_price);
    }

    public function test_add_cart_item_rejects_unavailable_product(): void
    {
        $product = Product::factory()->unavailable()->create();

        $this->expectException(ProductUnavailableException::class);

        $this->service->addCartItem($this->customer, $product->id, null, 1);
    }

    // ─── updateCartItem Tests ──────────────────────────────────────────

    public function test_update_cart_item_recalculates_price(): void
    {
        $product = Product::factory()->create(['base_price' => 15.00]);
        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 2,
            'unit_price' => 15.00,
            'total_price' => 30.00,
        ]);

        $updated = $this->service->updateCartItem($cartItem, 5);

        $this->assertEquals(5, (float) $updated->quantity);
        $this->assertEquals(15.00, (float) $updated->unit_price);
        $this->assertEquals(75.00, (float) $updated->total_price);
    }

    // ─── removeCartItem Tests ──────────────────────────────────────────

    public function test_remove_cart_item_deletes_item(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        $cartItem = CartItem::factory()->create(['cart_id' => $cart->id]);

        $this->service->removeCartItem($cartItem);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    // ─── clearCart Tests ───────────────────────────────────────────────

    public function test_clear_cart_removes_all_items(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->customer->id]);
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $this->service->clearCart($cart);

        $this->assertDatabaseCount('cart_items', 0);
    }

    // ─── Multi-Store Cart Tests ────────────────────────────────────────

    public function test_cart_can_have_items_from_multiple_stores(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();
        $product1 = Product::factory()->create(['store_id' => $store1->id, 'base_price' => 10.00]);
        $product2 = Product::factory()->create(['store_id' => $store2->id, 'base_price' => 20.00]);

        $item1 = $this->service->addCartItem($this->customer, $product1->id, null, 1);
        $item2 = $this->service->addCartItem($this->customer, $product2->id, null, 1);

        $cart = $this->service->getCart($this->customer);

        $this->assertCount(2, $cart->items);
        $storeIds = $cart->items->pluck('product.store_id')->unique()->values()->toArray();
        $this->assertCount(2, $storeIds);
        $this->assertContains($store1->id, $storeIds);
        $this->assertContains($store2->id, $storeIds);
    }
}

