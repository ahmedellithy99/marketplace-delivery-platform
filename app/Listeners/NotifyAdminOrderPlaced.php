<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\User;
use App\Services\NotificationService;

class NotifyAdminOrderPlaced
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $this->notificationService->createNotification(
                user: $admin,
                type: 'new_order',
                title: 'New Order Received',
                body: "A new order #{$order->order_number} has been placed.",
            );
        }
    }
}
