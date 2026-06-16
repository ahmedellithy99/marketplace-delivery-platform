<?php

namespace Tests\Unit\Listeners;

use App\Events\DeliveryAssigned;
use App\Jobs\SendWhatsAppMessageJob;
use App\Listeners\SendDeliveryAssignedWhatsApp;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SendDeliveryAssignedWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_whatsapp_job_for_delivery_man(): void
    {
        Bus::fake([SendWhatsAppMessageJob::class]);
        Config::set('app.url', 'https://example.com');

        $customer = User::factory()->create(['phone' => '1111111111']);
        $deliveryMan = User::factory()->delivery()->create(['phone' => '2222222222']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-456',
        ]);
        $delivery = Delivery::factory()->create([
            'order_id' => $order->id,
            'delivery_man_id' => $deliveryMan->id,
        ]);

        $event = new DeliveryAssigned($delivery);
        $listener = new SendDeliveryAssignedWhatsApp;

        $listener->handle($event);

        Bus::assertDispatched(SendWhatsAppMessageJob::class, function ($job) {
            return $job->phone === '22222222222'
                && str_contains($job->message, 'تم تعيينك لتوصيل طلب #ORD-456')
                && str_contains($job->message, 'https://example.com/delivery/assignments/');
        });
    }

    public function test_it_does_not_dispatch_when_delivery_man_is_missing(): void
    {
        Bus::fake([SendWhatsAppMessageJob::class]);

        $delivery = new Delivery;
        $delivery->delivery_man_id = null;
        $delivery->order_id = null;

        $event = new DeliveryAssigned($delivery);
        $listener = new SendDeliveryAssignedWhatsApp;

        $listener->handle($event);

        Bus::assertNotDispatched(SendWhatsAppMessageJob::class);
    }
}
