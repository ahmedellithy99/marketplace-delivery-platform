<?php

namespace Tests\Feature\Filters;

use App\Filters\Public\StoreFilter;
use App\Models\Store;
use App\Models\StoreType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class StoreFilterTest extends TestCase
{
    use RefreshDatabase;

    private function createFilter(array $params = []): StoreFilter
    {
        $request = new Request($params);

        return new StoreFilter($request);
    }

    // ─── Base Scope Tests ──────────────────────────────────────────────

    public function test_excludes_soft_deleted_stores(): void
    {
        Store::factory()->create(['name' => 'Active Store']);
        $deleted = Store::factory()->create(['name' => 'Deleted Store']);
        $deleted->delete();

        $filter = $this->createFilter();
        $results = Store::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Active Store', $results->first()->name);
    }

    // ─── Search Filter Tests ───────────────────────────────────────────

    public function test_search_filters_by_name(): void
    {
        Store::factory()->create(['name' => 'Fresh Market']);
        Store::factory()->create(['name' => 'City Pharmacy']);
        Store::factory()->create(['name' => 'Fresh Bakery']);

        $filter = $this->createFilter(['search' => 'Fresh']);
        $results = Store::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->pluck('name')->contains('Fresh Market'));
        $this->assertTrue($results->pluck('name')->contains('Fresh Bakery'));
    }

    public function test_search_is_case_insensitive(): void
    {
        Store::factory()->create(['name' => 'Fresh Market']);

        $filter = $this->createFilter(['search' => 'fresh']);
        $results = Store::filter($filter)->get();

        $this->assertCount(1, $results);
    }

    // ─── Type Filter Tests ─────────────────────────────────────────────

    public function test_type_filter_by_store_type_id(): void
    {
        $restaurant = StoreType::factory()->create(['name' => 'Restaurant']);
        $pharmacy = StoreType::factory()->create(['name' => 'Pharmacy']);

        Store::factory()->create(['store_type_id' => $restaurant->id]);
        Store::factory()->create(['store_type_id' => $restaurant->id]);
        Store::factory()->create(['store_type_id' => $pharmacy->id]);

        $filter = $this->createFilter(['type' => $restaurant->id]);
        $results = Store::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($s) => $s->store_type_id === $restaurant->id));
    }

    // ─── Sort Filter Tests ─────────────────────────────────────────────

    public function test_sort_by_name_asc(): void
    {
        Store::factory()->create(['name' => 'Charlie Store']);
        Store::factory()->create(['name' => 'Alpha Store']);
        Store::factory()->create(['name' => 'Beta Store']);

        $filter = $this->createFilter(['sort' => 'name']);
        $results = Store::filter($filter)->get();

        $this->assertEquals('Alpha Store', $results[0]->name);
        $this->assertEquals('Beta Store', $results[1]->name);
        $this->assertEquals('Charlie Store', $results[2]->name);
    }

    public function test_sort_by_name_desc(): void
    {
        Store::factory()->create(['name' => 'Charlie Store']);
        Store::factory()->create(['name' => 'Alpha Store']);
        Store::factory()->create(['name' => 'Beta Store']);

        $filter = $this->createFilter(['sort' => '-name']);
        $results = Store::filter($filter)->get();

        $this->assertEquals('Charlie Store', $results[0]->name);
        $this->assertEquals('Beta Store', $results[1]->name);
        $this->assertEquals('Alpha Store', $results[2]->name);
    }

    public function test_sort_by_latest(): void
    {
        $oldest = Store::factory()->create(['created_at' => now()->subDays(3)]);
        $middle = Store::factory()->create(['created_at' => now()->subDays(1)]);
        $newest = Store::factory()->create(['created_at' => now()]);

        $filter = $this->createFilter(['sort' => '-created_at']);
        $results = Store::filter($filter)->get();

        $this->assertEquals($newest->id, $results[0]->id);
        $this->assertEquals($middle->id, $results[1]->id);
        $this->assertEquals($oldest->id, $results[2]->id);
    }

    // ─── Nearby Filter Tests ───────────────────────────────────────────

    public function test_nearby_filter_returns_stores_within_radius(): void
    {
        // Store at approximately 33.5, 36.3 (Damascus area)
        Store::factory()->create([
            'name' => 'Nearby Store',
            'latitude' => 33.5138,
            'longitude' => 36.2765,
        ]);

        // Store far away (approximately 200km away)
        Store::factory()->create([
            'name' => 'Far Store',
            'latitude' => 35.5,
            'longitude' => 38.0,
        ]);

        $filter = $this->createFilter([
            'nearby' => ['lat' => 33.51, 'lng' => 36.28, 'radius' => 5],
        ]);
        $results = Store::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Nearby Store', $results->first()->name);
    }

    public function test_nearby_filter_ignores_invalid_input(): void
    {
        Store::factory()->count(3)->create();

        // Missing lat/lng
        $filter = $this->createFilter(['nearby' => 'invalid']);
        $results = Store::filter($filter)->get();

        $this->assertCount(3, $results);
    }

    public function test_nearby_filter_uses_default_radius(): void
    {
        // Store within 10km default radius
        Store::factory()->create([
            'name' => 'Close Store',
            'latitude' => 33.5138,
            'longitude' => 36.2765,
        ]);

        $filter = $this->createFilter([
            'nearby' => ['lat' => 33.51, 'lng' => 36.28],
        ]);
        $results = Store::filter($filter)->get();

        $this->assertCount(1, $results);
    }

    // ─── Combined Filters Test ─────────────────────────────────────────

    public function test_multiple_filters_combined(): void
    {
        $restaurant = StoreType::factory()->create(['name' => 'Restaurant']);
        $pharmacy = StoreType::factory()->create(['name' => 'Pharmacy']);

        Store::factory()->create([
            'name' => 'Fresh Restaurant',
            'store_type_id' => $restaurant->id,
        ]);
        Store::factory()->create([
            'name' => 'Fresh Pharmacy',
            'store_type_id' => $pharmacy->id,
        ]);
        Store::factory()->create([
            'name' => 'City Restaurant',
            'store_type_id' => $restaurant->id,
        ]);

        $filter = $this->createFilter([
            'search' => 'Fresh',
            'type' => $restaurant->id,
        ]);
        $results = Store::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Fresh Restaurant', $results->first()->name);
    }

    // ─── Empty/Null Filter Values ──────────────────────────────────────

    public function test_empty_search_does_not_filter(): void
    {
        Store::factory()->count(3)->create();

        $filter = $this->createFilter(['search' => '']);
        $results = Store::filter($filter)->get();

        $this->assertCount(3, $results);
    }

    public function test_null_filter_values_are_ignored(): void
    {
        Store::factory()->count(3)->create();

        $filter = $this->createFilter(['type' => null, 'search' => null]);
        $results = Store::filter($filter)->get();

        $this->assertCount(3, $results);
    }
}
