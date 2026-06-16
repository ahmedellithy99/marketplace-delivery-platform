<?php

namespace App\Listeners;

use App\Events\DeliveryAssigned;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDeliveryAssignedWhatsApp implements ShouldQueue
{
    public $queue = 'whatsapp';

    /**
     * Handle the event.
     */
    public function handle(DeliveryAssigned $event): void
    {
        $delivery = $event->delivery;
        $deliveryMan = $delivery->deliveryMan;

        if (!$deliveryMan) {
            return;
        }

        $order = $delivery->order;
        $customerName = $order->user->name ?? 'عميل';

        SendWhatsAppMessageJob::dispatch(
            phone: '2' . $deliveryMan->phone,
            message: "🛵 تم تعيينك لتوصيل طلب #{$order->order_number} للعميل {$customerName}\n" . config('app.url') . "/delivery/assignments/{$delivery->id}",
        );
    }
}
