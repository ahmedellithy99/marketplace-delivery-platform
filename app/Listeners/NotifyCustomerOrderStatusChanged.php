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

        $statusLabels = [
            'accepted' => 'تم قبول',
            'preparing' => 'جاري تحضير',
            'on_the_way' => 'في الطريق',
            'delivered' => 'تم توصيل',
            'cancelled' => 'تم إلغاء',
        ];

        $label = $statusLabels[$event->newStatus] ?? $event->newStatus;

        $this->notificationService->createNotification(
            user: $customer,
            type: 'order_' . $event->newStatus,
            title: "{$label} طلبك",
            body: "طلبك #{$order->order_number} — {$label}",
            link: "/orders/{$order->id}",
        );
    }
}
