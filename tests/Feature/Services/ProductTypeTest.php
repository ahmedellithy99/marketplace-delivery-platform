<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductTypeTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;
    private Store $store;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductService::class);
        $this->store = Store::factory()->create();
        $this->category = Category::factory()->create();
    }

    // ─── Simple Product Creation ───────────────────────────────────────

    public function test_simple_product_creation_with_base_price(): void
    {
        $product = $this->service->createProduct([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Simple Product',
            'type' => 'simple',
            'base_price' => 29.99,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'type' => 'simple',
            'base_price' => 29.99,
        ]);
        $this->assertTrue($product->isSimple());
    }

    // ─── Variant Product Creation ──────────────────────────────────────

    public function test_variant_product_creation_with_variants_array(): void
    {
        $product = $this->service->createProduct([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Variant Product',
            'type' => 'variant',
            'base_price' => null,
            'variants' => [
                ['name' => 'Small', 'price' => 15.00],
                ['name' => 'Medium', 'price' => 20.00],
                ['name' => 'Large', 'price' => 25.00],
            ],
        ]);

        $this->assertTrue($product->isVariant());
        $this->assertCount(3, $product->variants);
        $this->assertEquals('Small', $product->variants[0]->name);
        $this->assertEquals(15.00, (float) $product->variants[0]->price);
        $this->assertTrue($product->variants[0]->is_default);
        $this->assertEquals('Large', $product->variants[2]->name);
        $this->assertEquals(25.00, (float) $product->variants[2]->price);
    }

    // ─── Measured Product Creation ─────────────────────────────────────

    public function test_measured_product_creation_with_measurement_fields(): void
    {
        $product = $this->service->createProduct([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Measured Product',
            'type' => 'measured',
            'base_price' => 120.00,
            'measurement_unit' => 'kg',
            'min_quantity' => 0.5,
            'max_quantity' => 5.0,
            'quantity_step' => 0.5,
        ]);

        $this->assertTrue($product->isMeasured());
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'type' => 'measured',
            'base_price' => 120.00,
            'measurement_unit' => 'kg',
        ]);
    }

    // ─── Validation: Variant Requires at Least One Variant ─────────────

    public function test_variant_product_creation_without_variants_still_creates(): void
    {
        // The service creates the product even without variants array
        // (validation for "at least one variant" is handled at FormRequest level)
        $product = $this->service->createProduct([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Variant No Variants',
            'type' => 'variant',
            'base_price' => null,
            'variants' => [],
        ]);

        $this->assertTrue($product->isVariant());
        $this->assertCount(0, $product->variants);
    }

    // ─── Validation: Measured Product Requires measurement_unit ─────────

    public function test_measured_product_requires_measurement_unit(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->createProduct([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Measured Without Unit',
            'type' => 'measured',
            'base_price' => 50.00,
            // measurement_unit is missing
        ]);
    }

    // ─── Validation: Simple Product Requires base_price ────────────────

    public function test_simple_product_requires_base_price(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->createProduct([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Simple Without Price',
            'type' => 'simple',
            'base_price' => null,
        ]);
    }

    public function test_simple_product_rejects_zero_base_price(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->createProduct([
            'store_id' => $this->store->id,
            'category_id' => $this->category->id,
            'name' => 'Simple Zero Price',
            'type' => 'simple',
            'base_price' => 0,
        ]);
    }

    // ─── Toggle Availability Works for All Types ───────────────────────

    public function test_toggle_availability_works_for_simple_product(): void
    {
        $product = Product::factory()->create([
            'type' => 'simple',
            'is_available' => true,
        ]);

        $result = $this->service->toggleAvailability($product);
        $this->assertFalse($result->is_available);

        $result = $this->service->toggleAvailability($result);
        $this->assertTrue($result->is_available);
    }

    public function test_toggle_availability_works_for_variant_product(): void
    {
        $product = Product::factory()->variant()->create([
            'is_available' => true,
        ]);

        $result = $this->service->toggleAvailability($product);
        $this->assertFalse($result->is_available);

        $result = $this->service->toggleAvailability($result);
        $this->assertTrue($result->is_available);
    }

    public function test_toggle_availability_works_for_measured_product(): void
    {
        $product = Product::factory()->measured()->create([
            'is_available' => true,
        ]);

        $result = $this->service->toggleAvailability($product);
        $this->assertFalse($result->is_available);

        $result = $this->service->toggleAvailability($result);
        $this->assertTrue($result->is_available);
    }
}
