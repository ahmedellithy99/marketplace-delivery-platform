<?php

namespace Tests\Feature\Filters;

use App\Filters\Admin\CustomerFilter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CustomerFilterTest extends TestCase
{
    use RefreshDatabase;

    private function createFilter(array $params = []): CustomerFilter
    {
        return new CustomerFilter(new Request($params));
    }

    public function test_search_filters_by_name(): void
    {
        User::factory()->create(['role' => 'customer', 'name' => 'أحمد محمد']);
        User::factory()->create(['role' => 'customer', 'name' => 'علي حسن']);

        $filter = $this->createFilter(['search' => 'أحمد']);
        $results = User::where('role', 'customer')->filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('أحمد محمد', $results->first()->name);
    }

    public function test_search_filters_by_phone(): void
    {
        User::factory()->create(['role' => 'customer', 'phone' => '01000000001']);
        User::factory()->create(['role' => 'customer', 'phone' => '01000000002']);

        $filter = $this->createFilter(['search' => '0001']);
        $results = User::where('role', 'customer')->filter($filter)->get();

        $this->assertCount(1, $results);
    }

    public function test_search_filters_by_email(): void
    {
        User::factory()->create(['role' => 'customer', 'email' => 'ahmed@example.com']);
        User::factory()->create(['role' => 'customer', 'email' => 'ali@example.com']);

        $filter = $this->createFilter(['search' => 'ahmed']);
        $results = User::where('role', 'customer')->filter($filter)->get();

        $this->assertCount(1, $results);
    }

    public function test_search_is_case_insensitive(): void
    {
        User::factory()->create(['role' => 'customer', 'name' => 'Ahmed Test']);

        $filter = $this->createFilter(['search' => 'ahmed']);
        $results = User::where('role', 'customer')->filter($filter)->get();

        $this->assertCount(1, $results);
    }

    public function test_empty_search_returns_all(): void
    {
        User::factory()->count(3)->create(['role' => 'customer']);

        $filter = $this->createFilter(['search' => '']);
        $results = User::where('role', 'customer')->filter($filter)->get();

        $this->assertCount(3, $results);
    }

    public function test_search_does_not_return_non_customers(): void
    {
        User::factory()->create(['role' => 'customer', 'name' => 'Same Name']);
        User::factory()->create(['role' => 'admin', 'name' => 'Same Name']);

        $filter = $this->createFilter(['search' => 'Same Name']);
        $results = User::where('role', 'customer')->filter($filter)->get();

        $this->assertCount(1, $results);
    }

    public function test_null_filter_values_are_ignored(): void
    {
        User::factory()->count(2)->create(['role' => 'customer']);

        $filter = $this->createFilter(['search' => null]);
        $results = User::where('role', 'customer')->filter($filter)->get();

        $this->assertCount(2, $results);
    }
}
