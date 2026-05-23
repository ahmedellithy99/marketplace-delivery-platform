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
        $customerName = $order->user->name ?? 'عميل';

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $this->notificationService->createNotification(
                user: $admin,
                type: 'new_order',
                title: 'طلب جديد',
                body: "طلب جديد #{$order->order_number} من {$customerName}",
                link: "/admin/orders/{$order->id}",
            );
        }
    }
}
