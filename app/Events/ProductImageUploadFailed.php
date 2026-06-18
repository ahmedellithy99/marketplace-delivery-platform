<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ProductImageUploadFailed
{
    use Dispatchable;

    public function __construct(
        public int $productId,
        public string $reason,
    ) {
    }
}
