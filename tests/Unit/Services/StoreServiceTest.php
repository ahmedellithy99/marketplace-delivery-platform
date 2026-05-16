<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreType;
use App\Services\StoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StoreServiceTest extends TestCase
{
    use RefreshDatabase;

    private StoreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StoreService();
    }

    // ─── Create Tests ──────────────────────────────────────────────────

    public function test_create_persists_store_with_all_fields(): void
    {
        $storeType = StoreType::factory()->create();

        $data = [
            'name' => 'My Test Store',
            'store_type_id' => $storeType->id,
            'phone' => '0912345678',
            'address' => '123 Main Street',
            'latitude' => 33.5138073,
            'longitude' => 36.2765279,
            'opening_time' => '08:00',
            'closing_time' => '22:00',
        ];

        $store = $this->service->createStore($data);

        $this->assertInstanceOf(Store::class, $store);
        $this->assertTrue($store->exists);
        $this->assertEquals('My Test Store', $store->name);
        $this->assertEquals($storeType->id, $store->store_type_id);
        $this->assertEquals('0912345678', $store->phone);
    }

    public function test_create_generates_slug_automatically(): void
    {
        $storeType = StoreType::factory()->create();

        $store = $this->service->createStore([
            'name' => 'Fresh Market Store',
            'store_type_id' => $storeType->id,
            'phone' => '0912345678',
            'address' => '123 Main Street',
            'latitude' => 33.5138073,
            'longitude' => 36.2765279,
            'opening_time' => '08:00',
            'closing_time' => '22:00',
        ]);

        $this->assertNotEmpty($store->slug);
        $this->assertStringContainsString('fresh', $store->slug);
    }

    public function test_create_rejects_invalid_operating_hours(): void
    {
        $storeType = StoreType::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->createStore([
            'name' => 'Late Store',
            'store_type_id' => $storeType->id,
            'phone' => '0912345678',
            'address' => '123 Main Street',
            'latitude' => 33.5138073,
            'longitude' => 36.2765279,
            'opening_time' => '22:00',
            'closing_time' => '08:00',
        ]);
    }

    public function test_create_rejects_equal_operating_hours(): void
    {
        $storeType = StoreType::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->createStore([
            'name' => 'Equal Hours Store',
            'store_type_id' => $storeType->id,
            'phone' => '0912345678',
            'address' => '123 Main Street',
            'latitude' => 33.5138073,
            'longitude' => 36.2765279,
            'opening_time' => '10:00',
            'closing_time' => '10:00',
        ]);
    }

    // ─── Update Tests ──────────────────────────────────────────────────

    public function test_update_persists_changes(): void
    {
        $store = Store::factory()->create(['name' => 'Old Name']);

        $updated = $this->service->updateStore($store, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('stores', ['id' => $store->id, 'name' => 'New Name']);
    }

    public function test_update_regenerates_slug_on_name_change(): void
    {
        $store = Store::factory()->create(['name' => 'Original Store']);
        $originalSlug = $store->slug;

        $updated = $this->service->updateStore($store, ['name' => 'Completely Different Name']);

        $this->assertNotEquals($originalSlug, $updated->slug);
        $this->assertStringContainsString('completely', $updated->slug);
    }

    public function test_update_validates_operating_hours(): void
    {
        $store = Store::factory()->create([
            'opening_time' => '08:00',
            'closing_time' => '22:00',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->updateStore($store, [
            'opening_time' => '23:00',
            'closing_time' => '07:00',
        ]);
    }

    public function test_update_validates_hours_with_partial_update(): void
    {
        $store = Store::factory()->create([
            'opening_time' => '08:00',
            'closing_time' => '22:00',
        ]);

        // Updating only closing_time to be before existing opening_time
        $this->expectException(ValidationException::class);

        $this->service->updateStore($store, [
            'closing_time' => '07:00',
        ]);
    }

    // ─── Delete Tests ──────────────────────────────────────────────────

    public function test_delete_soft_deletes_store(): void
    {
        $store = Store::factory()->create();

        $this->service->deleteStore($store);

        $this->assertSoftDeleted('stores', ['id' => $store->id]);
    }

    public function test_delete_cascades_to_products(): void
    {
        $store = Store::factory()->create();
        $products = Product::factory()->count(3)->create(['store_id' => $store->id]);

        $this->service->deleteStore($store);

        foreach ($products as $product) {
            $this->assertSoftDeleted('products', ['id' => $product->id]);
        }
    }

    public function test_delete_does_not_affect_other_store_products(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        $product1 = Product::factory()->create(['store_id' => $store1->id]);
        $product2 = Product::factory()->create(['store_id' => $store2->id]);

        $this->service->deleteStore($store1);

        $this->assertSoftDeleted('products', ['id' => $product1->id]);
        $this->assertDatabaseHas('products', ['id' => $product2->id, 'deleted_at' => null]);
    }

    // ─── Operating Hours Validation Tests ──────────────────────────────

    public function test_valid_operating_hours_accepted(): void
    {
        $storeType = StoreType::factory()->create();

        $store = $this->service->createStore([
            'name' => 'Valid Hours Store',
            'store_type_id' => $storeType->id,
            'phone' => '0912345678',
            'address' => '123 Main Street',
            'latitude' => 33.5138073,
            'longitude' => 36.2765279,
            'opening_time' => '06:00',
            'closing_time' => '23:00',
        ]);

        $this->assertNotNull($store->id);
    }
}
