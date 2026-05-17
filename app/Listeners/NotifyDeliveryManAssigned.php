<?php

namespace App\Listeners;

use App\Events\DeliveryAssigned;
use App\Services\NotificationService;

class NotifyDeliveryManAssigned
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(DeliveryAssigned $event): void
    {
        $delivery = $event->delivery;
        $deliveryMan = $delivery->deliveryMan;
        $order = $delivery->order;

        $this->notificationService->createNotification(
            user: $deliveryMan,
            type: 'delivery_assigned',
            title: 'New Delivery Assigned',
            body: "You have been assigned to deliver order #{$order->order_number}.",
        );
    }
}
