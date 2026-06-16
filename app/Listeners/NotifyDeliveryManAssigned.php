<?php

namespace App\Listeners;

use App\Events\DeliveryAssigned;
use App\Services\NotificationService;
use App\Services\WaClientService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDeliveryManAssigned implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly WaClientService $waClient,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(DeliveryAssigned $event): void
    {
        $delivery = $event->delivery;
        $deliveryMan = $delivery->deliveryMan;
        $order = $delivery->order;
        $customerName = $order->user->name ?? 'عميل';

        $this->notificationService->createNotification(
            user: $deliveryMan,
            type: 'delivery_assigned',
            title: 'توصيل جديد',
            body: "تم تعيينك لتوصيل طلب #{$order->order_number} للعميل {$customerName}",
            link: "/delivery/assignments/{$delivery->id}",
        );

        $this->waClient->sendText(
            phone: '2' . $deliveryMan->phone,
            message: "🛵 تم تعيينك لتوصيل طلب #{$order->order_number} للعميل {$customerName}\n" . config('app.url') . "/delivery/assignments/{$delivery->id}",
        );
    }
}
