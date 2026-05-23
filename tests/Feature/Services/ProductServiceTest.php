<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService();
    }

    // ─── Create Tests ──────────────────────────────────────────────────

    public function test_create_product_with_valid_data(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create();

        $product = $this->service->createProduct([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'base_price' => 25.99,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'base_price' => 25.99,
        ]);
    }

    public function test_create_product_generates_slug(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create();

        $product = $this->service->createProduct([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Fresh Orange Juice',
            'base_price' => 5.00,
        ]);

        $this->assertNotNull($product->slug);
        $this->assertNotEmpty($product->slug);
    }

    public function test_create_product_rejects_nonexistent_store(): void
    {
        $category = Category::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->createProduct([
            'store_id' => 99999,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'base_price' => 10.00,
        ]);
    }

    public function test_create_product_rejects_nonexistent_category(): void
    {
        $store = Store::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->createProduct([
            'store_id' => $store->id,
            'category_id' => 99999,
            'name' => 'Test Product',
            'base_price' => 10.00,
        ]);
    }

    public function test_create_product_rejects_soft_deleted_store(): void
    {
        $store = Store::factory()->create();
        $store->delete(); // soft delete
        $category = Category::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->createProduct([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'base_price' => 10.00,
        ]);
    }

    public function test_create_product_rejects_soft_deleted_category(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create();
        $category->delete(); // soft delete

        $this->expectException(ValidationException::class);

        $this->service->createProduct([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'base_price' => 10.00,
        ]);
    }

    // ─── Update Tests ──────────────────────────────────────────────────

    public function test_update_product_with_valid_data(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name', 'base_price' => 20.00]);

        $updated = $this->service->updateProduct($product, [
            'name' => 'New Name',
            'base_price' => 30.00,
        ]);

        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals(30.00, (float) $updated->base_price);
    }

    public function test_update_product_validates_new_store_exists(): void
    {
        $product = Product::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->updateProduct($product, [
            'store_id' => 99999,
        ]);
    }

    public function test_update_product_validates_new_category_exists(): void
    {
        $product = Product::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->updateProduct($product, [
            'category_id' => 99999,
        ]);
    }

    // ─── Toggle Availability Tests ─────────────────────────────────────

    public function test_toggle_availability_makes_available_product_unavailable(): void
    {
        $product = Product::factory()->create(['is_available' => true]);

        $result = $this->service->toggleAvailability($product);

        $this->assertFalse($result->is_available);
    }

    public function test_toggle_availability_makes_unavailable_product_available(): void
    {
        $product = Product::factory()->create(['is_available' => false]);

        $result = $this->service->toggleAvailability($product);

        $this->assertTrue($result->is_available);
    }

    // ─── Variant CRUD Tests ────────────────────────────────────────────

    public function test_add_variant_to_product(): void
    {
        $product = Product::factory()->create();

        $variant = $this->service->addVariant($product, [
            'name' => 'Large',
            'price' => 15.99,
        ]);

        $this->assertInstanceOf(ProductVariant::class, $variant);
        $this->assertEquals($product->id, $variant->product_id);
        $this->assertEquals('Large', $variant->name);
        $this->assertEquals(15.99, (float) $variant->price);
    }

    public function test_update_variant(): void
    {
        $variant = ProductVariant::factory()->create([
            'name' => 'Small',
            'price' => 10.00,
        ]);

        $updated = $this->service->updateVariant($variant, [
            'name' => 'Medium',
            'price' => 15.00,
        ]);

        $this->assertEquals('Medium', $updated->name);
        $this->assertEquals(15.00, (float) $updated->price);
    }

    public function test_remove_variant(): void
    {
        $variant = ProductVariant::factory()->create();
        $variantId = $variant->id;

        $this->service->removeVariant($variant);

        $this->assertDatabaseMissing('product_variants', ['id' => $variantId]);
    }

    public function test_product_can_have_multiple_variants(): void
    {
        $product = Product::factory()->create();

        $this->service->addVariant($product, ['name' => 'Small', 'price' => 10.00]);
        $this->service->addVariant($product, ['name' => 'Medium', 'price' => 15.00]);
        $this->service->addVariant($product, ['name' => 'Large', 'price' => 20.00]);

        $this->assertCount(3, $product->fresh()->variants);
    }
}

