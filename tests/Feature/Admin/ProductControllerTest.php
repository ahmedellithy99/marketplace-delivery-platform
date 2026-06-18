<?php

namespace Tests\Feature\Admin;

use App\Jobs\UploadProductImagesJob;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_dispatches_image_upload_job_when_images_provided(): void
    {
        Bus::fake([UploadProductImagesJob::class]);
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $store = Store::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin);

        $response = $this->post('/admin/products', [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'type' => 'simple',
            'base_price' => 25.99,
            'images' => [
                UploadedFile::fake()->image('product1.jpg'),
                UploadedFile::fake()->image('product2.jpg'),
            ],
        ]);

        $response->assertRedirect('/admin/products');

        Bus::assertDispatched(UploadProductImagesJob::class, function ($job) {
            return $job->productId === 1 && count($job->tempPaths) === 2;
        });
    }

    public function test_store_does_not_dispatch_job_when_no_images(): void
    {
        Bus::fake([UploadProductImagesJob::class]);

        $admin = User::factory()->admin()->create();
        $store = Store::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin);

        $response = $this->post('/admin/products', [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'type' => 'simple',
            'base_price' => 25.99,
        ]);

        $response->assertRedirect('/admin/products');

        Bus::assertNotDispatched(UploadProductImagesJob::class);
    }
}
