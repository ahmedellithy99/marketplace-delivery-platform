<?php

namespace Tests\Unit\Services;

use App\Events\WhatsAppMessageFailed;
use App\Services\WaClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WaClientServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.waclient.access_token', 'test-token');
        config()->set('services.waclient.instance_id', 'test-instance');
        config()->set('services.waclient.timeout', 5);
        config()->set('services.waclient.log_success', false);
    }

    public function test_it_returns_false_when_credentials_are_missing(): void
    {
        config()->set('services.waclient.access_token', '');
        config()->set('services.waclient.instance_id', '');

        $service = new WaClientService;

        $this->assertFalse($service->sendText('201234567890', 'Hello'));
    }

    public function test_it_returns_false_when_message_is_empty(): void
    {
        $service = new WaClientService;

        $this->assertFalse($service->sendText('201234567890', ''));
        $this->assertFalse($service->sendText('201234567890', '   '));
    }

    public function test_it_returns_false_when_phone_is_invalid(): void
    {
        $service = new WaClientService;

        $this->assertFalse($service->sendText('123', 'Hello'));
        $this->assertFalse($service->sendText('abc123456789', 'Hello'));
        $this->assertFalse($service->sendText('+abc123456789', 'Hello'));
    }

    public function test_it_sends_message_successfully(): void
    {
        Http::fake([
            'https://waclient.com/api/send' => Http::response(['status' => 'success'], 200),
        ]);

        $service = new WaClientService;

        $this->assertTrue($service->sendText('201234567890', 'Hello'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://waclient.com/api/send'
                && $request['number'] === '201234567890'
                && $request['message'] === 'Hello'
                && $request['type'] === 'text'
                && $request['instance_id'] === 'test-instance'
                && $request['access_token'] === 'test-token';
        });
    }

    public function test_it_strips_plus_sign_from_phone(): void
    {
        Http::fake([
            'https://waclient.com/api/send' => Http::response(['status' => 'success'], 200),
        ]);

        $service = new WaClientService;

        $this->assertTrue($service->sendText('+201234567890', 'Hello'));

        Http::assertSent(function ($request) {
            return $request['number'] === '201234567890';
        });
    }

    public function test_it_returns_false_when_http_response_fails(): void
    {
        Event::fake([WhatsAppMessageFailed::class]);

        Http::fake([
            'https://waclient.com/api/send' => Http::response('Internal Server Error', 500),
        ]);

        $service = new WaClientService;

        $this->assertFalse($service->sendText('201234567890', 'Hello'));

        Event::assertDispatched(WhatsAppMessageFailed::class, function ($event) {
            return $event->phone === '201234567890'
                && str_contains($event->reason, 'http_exception_after_retries');
        });
    }

    public function test_it_returns_false_when_response_status_is_not_success(): void
    {
        Event::fake([WhatsAppMessageFailed::class]);

        Http::fake([
            'https://waclient.com/api/send' => Http::response(['status' => 'error', 'message' => 'Invalid number'], 200),
        ]);

        $service = new WaClientService;

        $this->assertFalse($service->sendText('201234567890', 'Hello'));

        Event::assertDispatched(WhatsAppMessageFailed::class, function ($event) {
            return $event->phone === '201234567890'
                && str_contains($event->reason, 'non_success_response');
        });
    }

    public function test_it_returns_false_when_request_throws_exception(): void
    {
        Event::fake([WhatsAppMessageFailed::class]);

        Http::fake([
            'https://waclient.com/api/send' => function () {
                throw new ConnectionException('Connection refused');
            },
        ]);

        $service = new WaClientService;

        $this->assertFalse($service->sendText('201234567890', 'Hello'));

        Event::assertDispatched(WhatsAppMessageFailed::class, function ($event) {
            return $event->phone === '201234567890'
                && str_contains($event->reason, 'http_exception_after_retries');
        });
    }

    public function test_it_respects_retry_after_header_on_rate_limit(): void
    {
        Event::fake([WhatsAppMessageFailed::class]);

        Http::fake([
            'https://waclient.com/api/send' => Http::response('Too Many Requests', 429, ['Retry-After' => '1']),
        ]);

        $service = new WaClientService;

        $start = microtime(true);
        $this->assertFalse($service->sendText('201234567890', 'Hello'));
        $elapsed = microtime(true) - $start;

        $this->assertGreaterThan(1, $elapsed);
        Event::assertDispatched(WhatsAppMessageFailed::class);
    }
}
