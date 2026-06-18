<?php

namespace Tests\Unit\Listeners;

use App\Events\ProductImageUploadFailed;
use App\Listeners\NotifyAdminProductImageUploadFailed;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotifyAdminProductImageUploadFailedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_notification_for_all_admins(): void
    {
        Event::fake([ProductImageUploadFailed::class]);

        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $product = Product::factory()->create(['name' => 'Test Product']);

        $event = new ProductImageUploadFailed($product->id, 'Upload error');
        $listener = new NotifyAdminProductImageUploadFailed(new \App\Services\NotificationService);

        $listener->handle($event);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin1->id,
            'type' => 'product_image_upload_failed',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin2->id,
            'type' => 'product_image_upload_failed',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $customer->id,
        ]);
    }
}
