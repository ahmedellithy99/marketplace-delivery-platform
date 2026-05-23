<?php

namespace Tests\Feature\Filters;

use App\Filters\Public\ProductFilter;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    private function createFilter(array $params = []): ProductFilter
    {
        $request = new Request($params);

        return new ProductFilter($request);
    }

    // ─── Base Scope Tests ──────────────────────────────────────────────

    public function test_excludes_unavailable_products(): void
    {
        Product::factory()->create(['is_available' => true, 'name' => 'Available']);
        Product::factory()->create(['is_available' => false, 'name' => 'Unavailable']);

        $filter = $this->createFilter();
        $results = Product::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Available', $results->first()->name);
    }

    public function test_excludes_soft_deleted_products(): void
    {
        $product = Product::factory()->create(['is_available' => true, 'name' => 'Active']);
        $deleted = Product::factory()->create(['is_available' => true, 'name' => 'Deleted']);
        $deleted->delete();

        $filter = $this->createFilter();
        $results = Product::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Active', $results->first()->name);
    }

    // ─── Search Filter Tests ───────────────────────────────────────────

    public function test_search_filters_by_name(): void
    {
        Product::factory()->create(['name' => 'Fresh Orange Juice', 'is_available' => true]);
        Product::factory()->create(['name' => 'Chocolate Cake', 'is_available' => true]);
        Product::factory()->create(['name' => 'Orange Smoothie', 'is_available' => true]);

        $filter = $this->createFilter(['search' => 'Orange']);
        $results = Product::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->pluck('name')->contains('Fresh Orange Juice'));
        $this->assertTrue($results->pluck('name')->contains('Orange Smoothie'));
    }

    public function test_search_is_case_insensitive(): void
    {
        Product::factory()->create(['name' => 'Fresh Milk', 'is_available' => true]);

        $filter = $this->createFilter(['search' => 'fresh']);
        $results = Product::filter($filter)->get();

        $this->assertCount(1, $results);
    }

    // ─── Category Filter Tests ─────────────────────────────────────────

    public function test_category_filter(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Product::factory()->create(['category_id' => $category1->id, 'is_available' => true]);
        Product::factory()->create(['category_id' => $category1->id, 'is_available' => true]);
        Product::factory()->create(['category_id' => $category2->id, 'is_available' => true]);

        $filter = $this->createFilter(['category' => $category1->id]);
        $results = Product::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($p) => $p->category_id === $category1->id));
    }

    // ─── Store Filter Tests ────────────────────────────────────────────

    public function test_store_filter(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        Product::factory()->create(['store_id' => $store1->id, 'is_available' => true]);
        Product::factory()->create(['store_id' => $store2->id, 'is_available' => true]);

        $filter = $this->createFilter(['store' => $store1->id]);
        $results = Product::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($store1->id, $results->first()->store_id);
    }

    // ─── Price Range Filter Tests ──────────────────────────────────────

    public function test_price_min_filter(): void
    {
        Product::factory()->create(['base_price' => 5.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 15.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 25.00, 'is_available' => true]);

        $filter = $this->createFilter(['price_min' => 10]);
        $results = Product::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($p) => (float) $p->base_price >= 10));
    }

    public function test_price_max_filter(): void
    {
        Product::factory()->create(['base_price' => 5.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 15.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 25.00, 'is_available' => true]);

        $filter = $this->createFilter(['price_max' => 20]);
        $results = Product::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($p) => (float) $p->base_price <= 20));
    }

    public function test_price_range_combined(): void
    {
        Product::factory()->create(['base_price' => 5.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 15.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 25.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 35.00, 'is_available' => true]);

        $filter = $this->createFilter(['price_min' => 10, 'price_max' => 30]);
        $results = Product::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($p) => (float) $p->base_price >= 10 && (float) $p->base_price <= 30));
    }

    // ─── Sort Filter Tests ─────────────────────────────────────────────

    public function test_sort_by_price_asc(): void
    {
        Product::factory()->create(['base_price' => 30.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 10.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 20.00, 'is_available' => true]);

        $filter = $this->createFilter(['sort' => 'base_price']);
        $results = Product::filter($filter)->get();

        $this->assertEquals(10.00, (float) $results[0]->base_price);
        $this->assertEquals(20.00, (float) $results[1]->base_price);
        $this->assertEquals(30.00, (float) $results[2]->base_price);
    }

    public function test_sort_by_price_desc(): void
    {
        Product::factory()->create(['base_price' => 30.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 10.00, 'is_available' => true]);
        Product::factory()->create(['base_price' => 20.00, 'is_available' => true]);

        $filter = $this->createFilter(['sort' => '-base_price']);
        $results = Product::filter($filter)->get();

        $this->assertEquals(30.00, (float) $results[0]->base_price);
        $this->assertEquals(20.00, (float) $results[1]->base_price);
        $this->assertEquals(10.00, (float) $results[2]->base_price);
    }

    public function test_sort_by_name_asc(): void
    {
        Product::factory()->create(['name' => 'Chocolate', 'is_available' => true]);
        Product::factory()->create(['name' => 'Apple', 'is_available' => true]);
        Product::factory()->create(['name' => 'Banana', 'is_available' => true]);

        $filter = $this->createFilter(['sort' => 'name']);
        $results = Product::filter($filter)->get();

        $this->assertEquals('Apple', $results[0]->name);
        $this->assertEquals('Banana', $results[1]->name);
        $this->assertEquals('Chocolate', $results[2]->name);
    }

    public function test_sort_by_name_desc(): void
    {
        Product::factory()->create(['name' => 'Chocolate', 'is_available' => true]);
        Product::factory()->create(['name' => 'Apple', 'is_available' => true]);
        Product::factory()->create(['name' => 'Banana', 'is_available' => true]);

        $filter = $this->createFilter(['sort' => '-name']);
        $results = Product::filter($filter)->get();

        $this->assertEquals('Chocolate', $results[0]->name);
        $this->assertEquals('Banana', $results[1]->name);
        $this->assertEquals('Apple', $results[2]->name);
    }

    public function test_sort_by_latest(): void
    {
        $oldest = Product::factory()->create(['is_available' => true, 'created_at' => now()->subDays(3)]);
        $middle = Product::factory()->create(['is_available' => true, 'created_at' => now()->subDays(1)]);
        $newest = Product::factory()->create(['is_available' => true, 'created_at' => now()]);

        $filter = $this->createFilter(['sort' => '-created_at']);
        $results = Product::filter($filter)->get();

        $this->assertEquals($newest->id, $results[0]->id);
        $this->assertEquals($middle->id, $results[1]->id);
        $this->assertEquals($oldest->id, $results[2]->id);
    }

    // ─── Combined Filters Test ─────────────────────────────────────────

    public function test_multiple_filters_combined(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Fresh Orange Juice',
            'base_price' => 15.00,
            'is_available' => true,
        ]);
        Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Orange Cake',
            'base_price' => 5.00,
            'is_available' => true,
        ]);
        Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Chocolate Milk',
            'base_price' => 15.00,
            'is_available' => true,
        ]);

        $filter = $this->createFilter([
            'search' => 'Orange',
            'store' => $store->id,
            'price_min' => 10,
        ]);
        $results = Product::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Fresh Orange Juice', $results->first()->name);
    }

    // ─── Empty/Null Filter Values ──────────────────────────────────────

    public function test_empty_search_does_not_filter(): void
    {
        Product::factory()->count(3)->create(['is_available' => true]);

        $filter = $this->createFilter(['search' => '']);
        $results = Product::filter($filter)->get();

        $this->assertCount(3, $results);
    }

    public function test_null_filter_values_are_ignored(): void
    {
        Product::factory()->count(3)->create(['is_available' => true]);

        $filter = $this->createFilter(['category' => null, 'store' => null]);
        $results = Product::filter($filter)->get();

        $this->assertCount(3, $results);
    }
}

