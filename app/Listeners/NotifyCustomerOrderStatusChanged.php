<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\NotificationService;

class NotifyCustomerOrderStatusChanged
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $customer = $order->user;

        $this->notificationService->createNotification(
            user: $customer,
            type: 'order_' . $event->newStatus,
            title: 'Order Status Updated',
            body: "Your order #{$order->order_number} has been updated to {$event->newStatus}.",
            link: "/orders/{$order->id}",
        );
    }
}
