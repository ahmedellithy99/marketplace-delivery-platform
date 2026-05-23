<?php

namespace Tests\Feature\Services;

use App\Services\DeliveryFeeService;
use Tests\TestCase;

class DeliveryFeeServiceTest extends TestCase
{
    private DeliveryFeeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DeliveryFeeService();
    }

    public function test_calculate_fee_range_returns_min_and_max(): void
    {
        $result = $this->service->calculateFeeRange(33.5138, 36.2765);

        $this->assertArrayHasKey('min', $result);
        $this->assertArrayHasKey('max', $result);
    }

    public function test_calculate_fee_range_min_is_less_than_or_equal_to_max(): void
    {
        $result = $this->service->calculateFeeRange(34.0, 36.5);

        $this->assertLessThanOrEqual($result['max'], $result['min']);
    }

    public function test_calculate_fee_range_values_are_non_negative(): void
    {
        $result = $this->service->calculateFeeRange(35.0, 38.0);

        $this->assertGreaterThanOrEqual(0, $result['min']);
        $this->assertGreaterThanOrEqual(0, $result['max']);
    }

    public function test_calculate_fee_range_max_increases_with_distance(): void
    {
        // Near the center
        $nearResult = $this->service->calculateFeeRange(33.5138, 36.2765);

        // Far from center
        $farResult = $this->service->calculateFeeRange(35.0, 38.0);

        $this->assertGreaterThanOrEqual($nearResult['max'], $farResult['max']);
    }

    public function test_calculate_fee_range_max_is_capped(): void
    {
        // Very far away coordinates
        $result = $this->service->calculateFeeRange(50.0, 50.0);

        $this->assertLessThanOrEqual(2000, $result['max']);
    }
}

