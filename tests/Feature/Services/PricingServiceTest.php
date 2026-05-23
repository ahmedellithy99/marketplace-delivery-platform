<?php

namespace Tests\Feature\Services;

use App\DTOs\PriceResult;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PricingService::class);
    }

    // ─── Simple Product (No Discount) ──────────────────────────────────

    public function test_simple_product_with_no_discount_returns_base_price(): void
    {
        $product = Product::factory()->create(['base_price' => 50.00]);
        $product->load('discounts');

        $result = $this->service->calculate($product);

        $this->assertInstanceOf(PriceResult::class, $result);
        $this->assertEquals(50.00, $result->unitPrice);
        $this->assertEquals(0, $result->discountAmount);
        $this->assertEquals(50.00, $result->effectivePrice);
        $this->assertEquals(50.00, $result->total);
        $this->assertFalse($result->hasDiscount());
    }

    // ─── Percentage Discount ───────────────────────────────────────────

    public function test_simple_product_with_percentage_discount_calculates_correctly(): void
    {
        $product = Product::factory()->create(['base_price' => 100.00]);

        $discount = Discount::create([
            'name' => 'Test 20% Off',
            'type' => 'percentage',
            'value' => 20,
            'scope' => 'product',
            'is_active' => true,
        ]);
        $product->discounts()->attach($discount->id);
        $product->load('discounts');

        $result = $this->service->calculate($product);

        $this->assertEquals(100.00, $result->unitPrice);
        $this->assertEquals(20.00, $result->discountAmount);
        $this->assertEquals(80.00, $result->effectivePrice);
        $this->assertEquals(80.00, $result->total);
        $this->assertTrue($result->hasDiscount());
    }

    // ─── Fixed Discount ────────────────────────────────────────────────

    public function test_simple_product_with_fixed_discount_calculates_correctly(): void
    {
        $product = Product::factory()->create(['base_price' => 75.00]);

        $discount = Discount::create([
            'name' => 'Test 15 EGP Off',
            'type' => 'fixed',
            'value' => 15,
            'scope' => 'product',
            'is_active' => true,
        ]);
        $product->discounts()->attach($discount->id);
        $product->load('discounts');

        $result = $this->service->calculate($product);

        $this->assertEquals(75.00, $result->unitPrice);
        $this->assertEquals(15.00, $result->discountAmount);
        $this->assertEquals(60.00, $result->effectivePrice);
        $this->assertEquals(60.00, $result->total);
        $this->assertTrue($result->hasDiscount());
    }

    // ─── Variant Product Uses Variant Price ────────────────────────────

    public function test_variant_product_uses_variant_price_not_base_price(): void
    {
        $product = Product::factory()->variant()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 35.00,
        ]);
        $product->load('discounts');
        $variant->load('discounts');

        $result = $this->service->calculate($product, $variant);

        $this->assertEquals(35.00, $result->unitPrice);
        $this->assertEquals(0, $result->discountAmount);
        $this->assertEquals(35.00, $result->effectivePrice);
        $this->assertEquals(35.00, $result->total);
    }

    // ─── Measured Product Multiplies by Quantity ────────────────────────

    public function test_measured_product_multiplies_by_quantity(): void
    {
        $product = Product::factory()->measured()->create(['base_price' => 40.00]);
        $product->load('discounts');

        $result = $this->service->calculate($product, null, 2.5);

        $this->assertEquals(40.00, $result->unitPrice);
        $this->assertEquals(100.00, $result->total);
    }

    // ─── Product-Level Discount Applies to Variant ─────────────────────

    public function test_product_level_discount_applies_to_variant_products(): void
    {
        $product = Product::factory()->variant()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 50.00,
        ]);

        $discount = Discount::create([
            'name' => 'Product Discount 10%',
            'type' => 'percentage',
            'value' => 10,
            'scope' => 'product',
            'is_active' => true,
        ]);
        $product->discounts()->attach($discount->id);
        $product->load('discounts');
        $variant->load('discounts');

        $result = $this->service->calculate($product, $variant);

        $this->assertEquals(50.00, $result->unitPrice);
        $this->assertEquals(5.00, $result->discountAmount);
        $this->assertEquals(45.00, $result->effectivePrice);
        $this->assertTrue($result->hasDiscount());
    }

    // ─── Best Discount Wins ────────────────────────────────────────────

    public function test_best_discount_wins_when_multiple_discounts_exist(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->variant()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 100.00,
        ]);

        // Variant-level discount: 30% off = 30 EGP savings
        $variantDiscount = Discount::create([
            'name' => 'Variant 30%',
            'type' => 'percentage',
            'value' => 30,
            'scope' => 'variant',
            'is_active' => true,
        ]);
        $variant->discounts()->attach($variantDiscount->id);

        // Product-level discount: 10% off = 10 EGP savings
        $productDiscount = Discount::create([
            'name' => 'Product 10%',
            'type' => 'percentage',
            'value' => 10,
            'scope' => 'product',
            'is_active' => true,
        ]);
        $product->discounts()->attach($productDiscount->id);

        // Category-level discount: 5 EGP fixed = 5 EGP savings
        $categoryDiscount = Discount::create([
            'name' => 'Category 5 EGP',
            'type' => 'fixed',
            'value' => 5,
            'scope' => 'category',
            'is_active' => true,
        ]);
        $category->discounts()->attach($categoryDiscount->id);

        // Store-level discount: 15% off = 15 EGP savings
        $storeDiscount = Discount::create([
            'name' => 'Store 15%',
            'type' => 'percentage',
            'value' => 15,
            'scope' => 'store',
            'is_active' => true,
        ]);
        $store->discounts()->attach($storeDiscount->id);

        // Load relationships for the service
        $product->load(['discounts', 'category', 'store']);
        $variant->load('discounts');

        $result = $this->service->calculate($product, $variant);

        // Variant discount (30%) gives the best savings (30 EGP)
        $this->assertEquals(30.00, $result->discountAmount);
        $this->assertEquals(70.00, $result->effectivePrice);
    }

    // ─── Expired Discount Not Applied ──────────────────────────────────

    public function test_expired_discount_is_not_applied(): void
    {
        $product = Product::factory()->create(['base_price' => 100.00]);

        $discount = Discount::create([
            'name' => 'Expired Discount',
            'type' => 'percentage',
            'value' => 50,
            'scope' => 'product',
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDays(1), // Expired yesterday
        ]);
        $product->discounts()->attach($discount->id);
        $product->load('discounts');

        $result = $this->service->calculate($product);

        $this->assertEquals(100.00, $result->unitPrice);
        $this->assertEquals(0, $result->discountAmount);
        $this->assertEquals(100.00, $result->effectivePrice);
        $this->assertFalse($result->hasDiscount());
    }

    // ─── Inactive Discount Not Applied ─────────────────────────────────

    public function test_inactive_discount_is_not_applied(): void
    {
        $product = Product::factory()->create(['base_price' => 100.00]);

        $discount = Discount::create([
            'name' => 'Inactive Discount',
            'type' => 'percentage',
            'value' => 50,
            'scope' => 'product',
            'is_active' => false,
        ]);
        $product->discounts()->attach($discount->id);
        $product->load('discounts');

        $result = $this->service->calculate($product);

        $this->assertEquals(100.00, $result->unitPrice);
        $this->assertEquals(0, $result->discountAmount);
        $this->assertEquals(100.00, $result->effectivePrice);
        $this->assertFalse($result->hasDiscount());
    }
}
