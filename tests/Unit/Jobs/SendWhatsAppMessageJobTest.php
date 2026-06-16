<?php

namespace Tests\Unit\Jobs;

use App\Events\WhatsAppMessageFailed;
use App\Jobs\SendWhatsAppMessageJob;
use App\Services\WaClientService;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class SendWhatsAppMessageJobTest extends TestCase
{
    public function test_job_succeeds_when_wa_client_returns_true(): void
    {
        $waClient = $this->createMock(WaClientService::class);
        $waClient->expects($this->once())
            ->method('sendText')
            ->with('201234567890', 'Hello')
            ->willReturn(true);

        $job = new SendWhatsAppMessageJob('201234567890', 'Hello');

        $job->handle($waClient);

        $this->assertTrue(true);
    }

    public function test_job_throws_exception_when_wa_client_returns_false(): void
    {
        $waClient = $this->createMock(WaClientService::class);
        $waClient->expects($this->once())
            ->method('sendText')
            ->willReturn(false);

        $job = new SendWhatsAppMessageJob('201234567890', 'Hello');

        $this->expectException(RuntimeException::class);

        $job->handle($waClient);
    }

    public function test_failed_method_dispatches_whatsapp_message_failed_event(): void
    {
        Event::fake([WhatsAppMessageFailed::class]);

        $job = new SendWhatsAppMessageJob('201234567890', 'Hello');
        $job->failed(new RuntimeException('Something went wrong'));

        Event::assertDispatched(WhatsAppMessageFailed::class, function ($event) {
            return $event->phone === '201234567890'
                && $event->message === 'Hello'
                && str_contains($event->reason, 'job_failed: Something went wrong');
        });
    }

    public function test_job_has_correct_tries_and_backoff(): void
    {
        $job = new SendWhatsAppMessageJob('201234567890', 'Hello');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 120], $job->backoff);
    }
}
