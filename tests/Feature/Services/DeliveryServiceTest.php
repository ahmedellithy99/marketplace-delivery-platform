<?php

namespace Tests\Feature\Services;

use App\Exceptions\DuplicateDeliveryException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Services\Delivery\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    private DeliveryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DeliveryService::class);
    }

    // ─── assignDelivery Tests ──────────────────────────────────────────

    public function test_assign_delivery_creates_record(): void
    {
        $order = Order::factory()->accepted()->create();
        $deliveryMan = User::factory()->delivery()->create();
        $admin = User::factory()->admin()->create();

        $delivery = $this->service->assignDelivery($order, $deliveryMan, $admin);

        $this->assertInstanceOf(Delivery::class, $delivery);
        $this->assertEquals($order->id, $delivery->order_id);
        $this->assertEquals($deliveryMan->id, $delivery->delivery_man_id);
        $this->assertEquals($admin->id, $delivery->assigned_by);
        $this->assertNotNull($delivery->assigned_at);
    }

    public function test_assign_delivery_rejects_duplicate(): void
    {
        $order = Order::factory()->accepted()->create();
        $deliveryMan = User::factory()->delivery()->create();
        $admin = User::factory()->admin()->create();

        // First assignment succeeds
        $this->service->assignDelivery($order, $deliveryMan, $admin);

        // Second assignment should throw
        $this->expectException(DuplicateDeliveryException::class);

        $anotherDeliveryMan = User::factory()->delivery()->create();
        $this->service->assignDelivery($order, $anotherDeliveryMan, $admin);
    }

    // ─── markPreparing Tests ───────────────────────────────────────────

    public function test_mark_preparing_transitions_order_status(): void
    {
        $order = Order::factory()->accepted()->create();
        $delivery = Delivery::factory()->create(['order_id' => $order->id]);

        $result = $this->service->markPreparing($delivery);

        $this->assertEquals('preparing', $result->order->status);
    }

    // ─── markPickedUp Tests ────────────────────────────────────────────

    public function test_mark_picked_up_transitions_and_sets_timestamp(): void
    {
        $order = Order::factory()->preparing()->create();
        $delivery = Delivery::factory()->create(['order_id' => $order->id]);

        $result = $this->service->markPickedUp($delivery);

        $this->assertEquals('on_the_way', $result->order->status);
        $this->assertNotNull($result->picked_up_at);
    }

    // ─── markDelivered Tests ───────────────────────────────────────────

    public function test_mark_delivered_transitions_and_sets_timestamp(): void
    {
        $order = Order::factory()->onTheWay()->create();
        $delivery = Delivery::factory()->create(['order_id' => $order->id]);

        $result = $this->service->markDelivered($delivery);

        $this->assertEquals('delivered', $result->order->status);
        $this->assertNotNull($result->delivered_at);
    }

    // ─── Status Skipping Tests ─────────────────────────────────────────

    public function test_cannot_skip_status_preparing_to_delivered(): void
    {
        $order = Order::factory()->accepted()->create();
        $delivery = Delivery::factory()->create(['order_id' => $order->id]);

        $this->expectException(InvalidStatusTransitionException::class);

        // Trying to mark as picked up (on_the_way) without going through preparing first
        $this->service->markPickedUp($delivery);
    }

    public function test_cannot_skip_status_accepted_to_delivered(): void
    {
        $order = Order::factory()->accepted()->create();
        $delivery = Delivery::factory()->create(['order_id' => $order->id]);

        $this->expectException(InvalidStatusTransitionException::class);

        // Trying to mark as delivered without going through preparing and on_the_way
        $this->service->markDelivered($delivery);
    }

    // ─── getDeliveries Tests ───────────────────────────────────────────

    public function test_get_deliveries_returns_paginated_results(): void
    {
        $deliveryMan = User::factory()->delivery()->create();
        Delivery::factory()->count(3)->create(['delivery_man_id' => $deliveryMan->id]);

        $request = new \Illuminate\Http\Request();
        $result = $this->service->getDeliveries($deliveryMan, $request);

        $this->assertCount(3, $result->items());
    }

    public function test_get_deliveries_only_returns_assigned_deliveries(): void
    {
        $deliveryMan = User::factory()->delivery()->create();
        $otherDeliveryMan = User::factory()->delivery()->create();

        Delivery::factory()->count(2)->create(['delivery_man_id' => $deliveryMan->id]);
        Delivery::factory()->count(3)->create(['delivery_man_id' => $otherDeliveryMan->id]);

        $request = new \Illuminate\Http\Request();
        $result = $this->service->getDeliveries($deliveryMan, $request);

        $this->assertCount(2, $result->items());
    }

    // ─── getDelivery Tests ─────────────────────────────────────────────

    public function test_get_delivery_loads_relationships(): void
    {
        $delivery = Delivery::factory()->create();

        $result = $this->service->getDelivery($delivery);

        $this->assertTrue($result->relationLoaded('order'));
        $this->assertTrue($result->order->relationLoaded('items'));
        $this->assertTrue($result->order->relationLoaded('user'));
    }
}

