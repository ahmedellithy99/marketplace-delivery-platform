<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Services\Admin\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountServiceTest extends TestCase
{
    use RefreshDatabase;

    private DiscountService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DiscountService::class);
    }

    // ─── Create Discount ───────────────────────────────────────────────

    public function test_create_discount_with_product_scope_and_attach_targets(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $discount = $this->service->createDiscount([
            'name' => 'Summer Sale',
            'type' => 'percentage',
            'value' => 25,
            'scope' => 'product',
            'is_active' => true,
            'target_ids' => [$product1->id, $product2->id],
        ]);

        $this->assertInstanceOf(Discount::class, $discount);
        $this->assertDatabaseHas('discounts', [
            'id' => $discount->id,
            'name' => 'Summer Sale',
            'type' => 'percentage',
            'value' => 25.00,
            'scope' => 'product',
            'is_active' => true,
        ]);

        // Verify targets are attached
        $this->assertDatabaseHas('discountables', [
            'discount_id' => $discount->id,
            'discountable_id' => $product1->id,
            'discountable_type' => Product::class,
        ]);
        $this->assertDatabaseHas('discountables', [
            'discount_id' => $discount->id,
            'discountable_id' => $product2->id,
            'discountable_type' => Product::class,
        ]);
    }

    public function test_create_discount_with_store_scope(): void
    {
        $store = Store::factory()->create();

        $discount = $this->service->createDiscount([
            'name' => 'Store-wide Discount',
            'type' => 'fixed',
            'value' => 10,
            'scope' => 'store',
            'is_active' => true,
            'target_ids' => [$store->id],
        ]);

        $this->assertDatabaseHas('discounts', [
            'id' => $discount->id,
            'name' => 'Store-wide Discount',
            'type' => 'fixed',
            'scope' => 'store',
        ]);
        $this->assertDatabaseHas('discountables', [
            'discount_id' => $discount->id,
            'discountable_id' => $store->id,
            'discountable_type' => Store::class,
        ]);
    }

    public function test_create_discount_with_category_scope(): void
    {
        $category = Category::factory()->create();

        $discount = $this->service->createDiscount([
            'name' => 'Category Promo',
            'type' => 'percentage',
            'value' => 15,
            'scope' => 'category',
            'is_active' => true,
            'target_ids' => [$category->id],
        ]);

        $this->assertDatabaseHas('discounts', [
            'id' => $discount->id,
            'scope' => 'category',
        ]);
        $this->assertDatabaseHas('discountables', [
            'discount_id' => $discount->id,
            'discountable_id' => $category->id,
            'discountable_type' => Category::class,
        ]);
    }

    public function test_create_discount_with_variant_scope(): void
    {
        $variant = ProductVariant::factory()->create();

        $discount = $this->service->createDiscount([
            'name' => 'Variant Special',
            'type' => 'fixed',
            'value' => 5,
            'scope' => 'variant',
            'is_active' => true,
            'target_ids' => [$variant->id],
        ]);

        $this->assertDatabaseHas('discountables', [
            'discount_id' => $discount->id,
            'discountable_id' => $variant->id,
            'discountable_type' => ProductVariant::class,
        ]);
    }

    // ─── Update Discount ───────────────────────────────────────────────

    public function test_update_discount_changes_values(): void
    {
        $discount = Discount::create([
            'name' => 'Original Name',
            'type' => 'percentage',
            'value' => 10,
            'scope' => 'product',
            'is_active' => true,
        ]);

        $updated = $this->service->updateDiscount($discount, [
            'name' => 'Updated Name',
            'value' => 25,
            'type' => 'fixed',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals(25.00, (float) $updated->value);
        $this->assertEquals('fixed', $updated->type);
    }

    public function test_update_discount_re_attaches_targets(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();

        $discount = $this->service->createDiscount([
            'name' => 'Test',
            'type' => 'percentage',
            'value' => 10,
            'scope' => 'product',
            'is_active' => true,
            'target_ids' => [$product1->id, $product2->id],
        ]);

        // Update with new targets
        $this->service->updateDiscount($discount, [
            'target_ids' => [$product3->id],
        ]);

        // Old targets should be removed
        $this->assertDatabaseMissing('discountables', [
            'discount_id' => $discount->id,
            'discountable_id' => $product1->id,
        ]);
        $this->assertDatabaseMissing('discountables', [
            'discount_id' => $discount->id,
            'discountable_id' => $product2->id,
        ]);
        // New target should exist
        $this->assertDatabaseHas('discountables', [
            'discount_id' => $discount->id,
            'discountable_id' => $product3->id,
            'discountable_type' => Product::class,
        ]);
    }

    // ─── Delete Discount ───────────────────────────────────────────────

    public function test_delete_discount_removes_it_and_discountables(): void
    {
        $product = Product::factory()->create();

        $discount = $this->service->createDiscount([
            'name' => 'To Delete',
            'type' => 'fixed',
            'value' => 5,
            'scope' => 'product',
            'is_active' => true,
            'target_ids' => [$product->id],
        ]);

        $discountId = $discount->id;

        $this->service->deleteDiscount($discount);

        $this->assertDatabaseMissing('discounts', ['id' => $discountId]);
        $this->assertDatabaseMissing('discountables', ['discount_id' => $discountId]);
    }

    // ─── Toggle Active ─────────────────────────────────────────────────

    public function test_toggle_active_deactivates_active_discount(): void
    {
        $discount = Discount::create([
            'name' => 'Active Discount',
            'type' => 'percentage',
            'value' => 10,
            'scope' => 'product',
            'is_active' => true,
        ]);

        $result = $this->service->toggleActive($discount);

        $this->assertFalse($result->is_active);
    }

    public function test_toggle_active_activates_inactive_discount(): void
    {
        $discount = Discount::create([
            'name' => 'Inactive Discount',
            'type' => 'percentage',
            'value' => 10,
            'scope' => 'product',
            'is_active' => false,
        ]);

        $result = $this->service->toggleActive($discount);

        $this->assertTrue($result->is_active);
    }

    // ─── Get Target Options ────────────────────────────────────────────

    public function test_get_target_options_returns_products_for_product_scope(): void
    {
        Product::factory()->count(3)->create();

        $options = $this->service->getTargetOptions('product');

        $this->assertCount(3, $options);
        $this->assertArrayHasKey('id', $options[0]);
        $this->assertArrayHasKey('name', $options[0]);
    }

    public function test_get_target_options_returns_stores_for_store_scope(): void
    {
        Store::factory()->count(2)->create();

        $options = $this->service->getTargetOptions('store');

        $this->assertCount(2, $options);
        $this->assertArrayHasKey('id', $options[0]);
        $this->assertArrayHasKey('name', $options[0]);
    }

    public function test_get_target_options_returns_categories_for_category_scope(): void
    {
        Category::factory()->count(4)->create();

        $options = $this->service->getTargetOptions('category');

        $this->assertCount(4, $options);
        $this->assertArrayHasKey('id', $options[0]);
        $this->assertArrayHasKey('name', $options[0]);
    }

    public function test_get_target_options_returns_variants_for_variant_scope(): void
    {
        ProductVariant::factory()->count(2)->create();

        $options = $this->service->getTargetOptions('variant');

        $this->assertCount(2, $options);
        $this->assertArrayHasKey('id', $options[0]);
        $this->assertArrayHasKey('price', $options[0]);
    }

    public function test_get_target_options_returns_empty_for_unknown_scope(): void
    {
        $options = $this->service->getTargetOptions('unknown');

        $this->assertEmpty($options);
    }
}
