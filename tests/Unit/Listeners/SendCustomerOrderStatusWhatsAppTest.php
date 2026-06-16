<?php

namespace Tests\Unit\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\SendWhatsAppMessageJob;
use App\Listeners\SendCustomerOrderStatusWhatsApp;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SendCustomerOrderStatusWhatsAppTest extends TestCase
{
    use RefreshDatabase;
    public function test_it_dispatches_whatsapp_job_for_customer(): void
    {
        Bus::fake([SendWhatsAppMessageJob::class]);
        Config::set('app.url', 'https://example.com');

        $customer = User::factory()->create(['phone' => '1234567890']);
        $order = Order::factory()->create(['user_id' => $customer->id, 'order_number' => 'ORD-123']);

        $event = new OrderStatusChanged($order, 'pending', 'on_the_way');
        $listener = new SendCustomerOrderStatusWhatsApp;

        $listener->handle($event);

        Bus::assertDispatched(SendWhatsAppMessageJob::class, function ($job) {
            return $job->phone === '21234567890'
                && str_contains($job->message, 'طلبك #ORD-123')
                && str_contains($job->message, 'في الطريق')
                && str_contains($job->message, 'https://example.com/orders/');
        });
    }

    public function test_it_does_not_dispatch_when_customer_is_missing(): void
    {
        Bus::fake([SendWhatsAppMessageJob::class]);

        $order = new Order;
        $order->user_id = null;
        $order->order_number = 'ORD-TEST';

        $event = new OrderStatusChanged($order, 'pending', 'accepted');
        $listener = new SendCustomerOrderStatusWhatsApp;

        $listener->handle($event);

        Bus::assertNotDispatched(SendWhatsAppMessageJob::class);
    }
}
