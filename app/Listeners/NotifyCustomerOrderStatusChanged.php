<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\NotificationService;
use App\Services\WaClientService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerOrderStatusChanged implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly WaClientService $waClient,
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

        $this->waClient->sendText(
            phone: '2' . $customer->phone,
            message: "🛵 طلبك #{$order->order_number} — {$label}\n" . config('app.url') . "/orders/{$order->id}",
        );
    }
}
