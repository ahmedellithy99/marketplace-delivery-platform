<?php

namespace Tests\Feature\Services;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderService::class);
    }

    // ─── transitionStatus Tests ────────────────────────────────────────

    public function test_transition_from_accepted_to_preparing(): void
    {
        $order = Order::factory()->accepted()->create();

        $result = $this->service->transitionStatus($order, 'preparing');

        $this->assertEquals('preparing', $result->status);
    }

    public function test_transition_from_preparing_to_on_the_way(): void
    {
        $order = Order::factory()->preparing()->create();

        $result = $this->service->transitionStatus($order, 'on_the_way');

        $this->assertEquals('on_the_way', $result->status);
    }

    public function test_transition_from_on_the_way_to_delivered(): void
    {
        $order = Order::factory()->onTheWay()->create();

        $result = $this->service->transitionStatus($order, 'delivered');

        $this->assertEquals('delivered', $result->status);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->transitionStatus($order, 'delivered');
    }

    public function test_cannot_skip_status_in_sequence(): void
    {
        $order = Order::factory()->accepted()->create();

        $this->expectException(InvalidStatusTransitionException::class);

        // Skipping preparing → on_the_way
        $this->service->transitionStatus($order, 'on_the_way');
    }

    public function test_cannot_transition_from_delivered(): void
    {
        $order = Order::factory()->delivered()->create();

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->transitionStatus($order, 'pending');
    }

    public function test_cannot_transition_from_cancelled(): void
    {
        $order = Order::factory()->cancelled()->create();

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->transitionStatus($order, 'pending');
    }

    // ─── acceptOrder Tests ─────────────────────────────────────────────

    public function test_accept_order_transitions_to_accepted(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'subtotal' => 100.00,
        ]);

        $result = $this->service->acceptOrder($order, 15.00);

        $this->assertEquals('accepted', $result->status);
    }

    public function test_accept_order_sets_delivery_fee(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'subtotal' => 100.00,
        ]);

        $result = $this->service->acceptOrder($order, 15.00);

        $this->assertEquals(15.00, (float) $result->delivery_fee);
    }

    public function test_accept_order_recalculates_total(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'subtotal' => 100.00,
            'total' => 120.00, // old estimated total
        ]);

        $result = $this->service->acceptOrder($order, 15.00);

        $this->assertEquals(115.00, (float) $result->total);
    }

    public function test_accept_order_rejects_non_pending_order(): void
    {
        $order = Order::factory()->accepted()->create();

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->acceptOrder($order, 15.00);
    }

    public function test_accept_order_rejects_zero_delivery_fee(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $this->expectException(ValidationException::class);

        $this->service->acceptOrder($order, 0);
    }

    public function test_accept_order_rejects_negative_delivery_fee(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $this->expectException(ValidationException::class);

        $this->service->acceptOrder($order, -5.00);
    }

    // ─── cancelOrder Tests ─────────────────────────────────────────────

    public function test_cancel_order_from_pending(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $result = $this->service->cancelOrder($order);

        $this->assertEquals('cancelled', $result->status);
    }

    public function test_cancel_order_from_accepted(): void
    {
        $order = Order::factory()->accepted()->create();

        $result = $this->service->cancelOrder($order);

        $this->assertEquals('cancelled', $result->status);
    }

    public function test_cancel_order_from_preparing(): void
    {
        $order = Order::factory()->preparing()->create();

        $result = $this->service->cancelOrder($order);

        $this->assertEquals('cancelled', $result->status);
    }

    public function test_cancel_order_from_on_the_way(): void
    {
        $order = Order::factory()->onTheWay()->create();

        $result = $this->service->cancelOrder($order);

        $this->assertEquals('cancelled', $result->status);
    }

    public function test_cancel_order_from_delivered_throws_exception(): void
    {
        $order = Order::factory()->delivered()->create();

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->cancelOrder($order);
    }

    public function test_cancel_order_from_cancelled_throws_exception(): void
    {
        $order = Order::factory()->cancelled()->create();

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->cancelOrder($order);
    }
}
