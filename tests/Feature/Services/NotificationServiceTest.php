<?php

namespace Tests\Feature\Services;

use App\Events\DeliveryAssigned;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Models\Delivery;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    // ─── createNotification Tests ──────────────────────────────────────

    public function test_create_notification_persists_record(): void
    {
        $user = User::factory()->customer()->create();

        $notification = $this->service->createNotification(
            user: $user,
            type: 'order_placed',
            title: 'Order Placed',
            body: 'Your order has been placed successfully.',
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'type' => 'order_placed',
            'title' => 'Order Placed',
            'body' => 'Your order has been placed successfully.',
            'is_read' => false,
        ]);
    }

    // ─── getNotifications Tests ────────────────────────────────────────

    public function test_get_notifications_returns_user_notifications(): void
    {
        $user = User::factory()->customer()->create();
        Notification::factory()->count(5)->create(['user_id' => $user->id]);

        // Create notifications for another user (should not appear)
        $otherUser = User::factory()->customer()->create();
        Notification::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $result = $this->service->getNotifications($user);

        $this->assertCount(5, $result->items());
    }

    public function test_get_notifications_returns_paginated_results(): void
    {
        $user = User::factory()->customer()->create();
        Notification::factory()->count(20)->create(['user_id' => $user->id]);

        $result = $this->service->getNotifications($user, perPage: 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    // ─── markAsRead Tests ──────────────────────────────────────────────

    public function test_mark_as_read_updates_is_read(): void
    {
        $notification = Notification::factory()->create(['is_read' => false]);

        $result = $this->service->markAsRead($notification);

        $this->assertTrue($result->is_read);
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    // ─── getUnreadCount Tests ──────────────────────────────────────────

    public function test_get_unread_count_returns_correct_count(): void
    {
        $user = User::factory()->customer()->create();
        Notification::factory()->count(3)->create(['user_id' => $user->id, 'is_read' => false]);
        Notification::factory()->count(2)->read()->create(['user_id' => $user->id]);

        $count = $this->service->getUnreadCount($user);

        $this->assertEquals(3, $count);
    }

    // ─── OrderPlaced Event → Admin Notification Tests ──────────────────

    public function test_order_placed_event_creates_admin_notification(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        event(new OrderPlaced($order));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'new_order',
            'title' => 'New Order Received',
        ]);
    }

    // ─── OrderStatusChanged Event → Customer Notification Tests ────────

    public function test_order_status_changed_event_creates_customer_notification(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);

        event(new OrderStatusChanged($order, 'pending', 'accepted'));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'order_accepted',
            'title' => 'Order Status Updated',
        ]);
    }

    // ─── DeliveryAssigned Event → Delivery Man Notification Tests ──────

    public function test_delivery_assigned_event_creates_delivery_man_notification(): void
    {
        $deliveryMan = User::factory()->delivery()->create();
        $order = Order::factory()->accepted()->create();
        $delivery = Delivery::factory()->create([
            'order_id' => $order->id,
            'delivery_man_id' => $deliveryMan->id,
        ]);

        event(new DeliveryAssigned($delivery));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $deliveryMan->id,
            'type' => 'delivery_assigned',
            'title' => 'New Delivery Assigned',
        ]);
    }
}
