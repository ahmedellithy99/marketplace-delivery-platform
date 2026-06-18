<?php

namespace Tests\Unit\Jobs;

use App\Events\ProductImageUploadFailed;
use App\Jobs\UploadProductImagesJob;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadProductImagesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_adds_images_to_product_and_cleans_up_temp_files(): void
    {
        Storage::fake('local');
        Cache::shouldReceive('forget')
            ->with("product_images_processing_1")
            ->once();

        $product = Product::factory()->create();
        $file = UploadedFile::fake()->image('product.jpg');
        $tempPath = $file->store('temp/product-images');

        $job = new UploadProductImagesJob($product->id, [$tempPath]);
        $job->handle();

        $this->assertCount(1, $product->fresh()->getMedia('images'));
        Storage::disk('local')->assertMissing($tempPath);
    }

    public function test_job_dispatches_failure_event_and_cleans_up_on_exception(): void
    {
        Event::fake([ProductImageUploadFailed::class]);
        Storage::fake('local');
        Cache::shouldReceive('forget')
            ->with("product_images_processing_99999")
            ->once();

        $file = UploadedFile::fake()->image('product.jpg');
        $tempPath = $file->store('temp/product-images');

        $job = new UploadProductImagesJob(99999, [$tempPath]);
        $job->failed(new \Exception('Something went wrong'));

        Event::assertDispatched(ProductImageUploadFailed::class, function ($event) {
            return $event->productId === 99999
                && str_contains($event->reason, 'Something went wrong');
        });

        Storage::disk('local')->assertMissing($tempPath);
    }
}
