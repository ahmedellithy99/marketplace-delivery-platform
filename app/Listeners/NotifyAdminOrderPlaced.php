<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WaClientService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminOrderPlaced implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly WaClientService $waClient,
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

            $this->waClient->sendText(
                phone: $admin->phone,
                message: "📦 طلب جديد #{$order->order_number} من {$customerName}\nاضغط لفتح الطلب: " . config('app.url') . "/admin/orders/{$order->id}",
            );
        }
    }
}
