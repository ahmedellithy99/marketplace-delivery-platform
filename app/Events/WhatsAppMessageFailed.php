<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WhatsAppMessageFailed
{
    use Dispatchable;

    public function __construct(
        public string $phone,
        public string $message,
        public string $reason,
        public ?string $traceId = null,
    ) {
    }
}
