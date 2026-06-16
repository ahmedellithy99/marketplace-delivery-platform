<?php

namespace App\Jobs;

use App\Events\WhatsAppMessageFailed;
use App\Services\WaClientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 120];

    public function __construct(
        public string $phone,
        public string $message,
    ) {
    }

    public function handle(WaClientService $waClient): void
    {
        $success = $waClient->sendText($this->phone, $this->message);

        if (!$success) {
            throw new \RuntimeException('WhatsApp message failed to send');
        }
    }

    public function failed(\Throwable $exception): void
    {
        WhatsAppMessageFailed::dispatch(
            $this->phone,
            $this->message,
            'job_failed: '.$exception->getMessage(),
        );
    }
}
