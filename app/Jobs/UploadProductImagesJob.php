<?php

namespace App\Jobs;

use App\Events\ProductImageUploadFailed;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UploadProductImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60];

    public function __construct(
        public int $productId,
        public array $tempPaths,
    ) {
    }

    public function handle(): void
    {
        $product = Product::find($this->productId);

        if (!$product) {
            $this->cleanupTempFiles();
            Cache::forget($this->getCacheKey());

            return;
        }

        try {
            foreach ($this->tempPaths as $path) {
                $fullPath = Storage::path($path);

                if (file_exists($fullPath)) {
                    $product->addMedia($fullPath)->toMediaCollection('images');
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
        } finally {
            Cache::forget($this->getCacheKey());
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->cleanupTempFiles();
        Cache::forget($this->getCacheKey());

        Log::error('Product image upload job failed', [
            'product_id' => $this->productId,
            'paths' => $this->tempPaths,
            'error' => $exception->getMessage(),
        ]);

        ProductImageUploadFailed::dispatch($this->productId, $exception->getMessage());
    }

    protected function cleanupTempFiles(): void
    {
        foreach ($this->tempPaths as $path) {
            $fullPath = Storage::path($path);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    protected function getCacheKey(): string
    {
        return "product_images_processing_{$this->productId}";
    }
}
