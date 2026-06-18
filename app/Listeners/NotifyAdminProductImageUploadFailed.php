<?php

namespace App\Listeners;

use App\Events\ProductImageUploadFailed;
use App\Models\Product;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminProductImageUploadFailed implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    public function handle(ProductImageUploadFailed $event): void
    {
        $product = Product::find($event->productId);
        $productName = $product?->name ?? "#{$event->productId}";

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $this->notificationService->createNotification(
                user: $admin,
                type: 'product_image_upload_failed',
                title: 'فشل رفع صور المنتج',
                body: "فشل رفع صور المنتج {$productName}. السبب: {$event->reason}",
                link: $product ? "/admin/products/{$product->id}/edit" : '/admin/products',
            );
        }
    }
}
