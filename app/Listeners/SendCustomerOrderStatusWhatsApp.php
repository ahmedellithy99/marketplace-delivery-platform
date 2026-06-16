<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCustomerOrderStatusWhatsApp implements ShouldQueue
{
    public $queue = 'whatsapp';

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $customer = $order->user;

        if (!$customer) {
            return;
        }

        $statusLabels = [
            'accepted' => 'تم قبول',
            'preparing' => 'جاري تحضير',
            'on_the_way' => 'في الطريق',
            'delivered' => 'تم توصيل',
            'cancelled' => 'تم إلغاء',
        ];

        $label = $statusLabels[$event->newStatus] ?? $event->newStatus;

        SendWhatsAppMessageJob::dispatch(
            phone: '2' . $customer->phone,
            message: "🛵 طلبك #{$order->order_number} — {$label}\n" . config('app.url') . "/orders/{$order->id}",
        );
    }
}
