<?php

namespace App\Services\Delivery;

use App\Exceptions\DuplicateDeliveryException;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Services\Admin\OrderService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class DeliveryService
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * Get paginated deliveries assigned to a delivery man with order details.
     */
    public function getDeliveries(User $deliveryMan, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return Delivery::with(['order.user', 'order.items'])
            ->where('delivery_man_id', $deliveryMan->id)
            ->orderByDesc('assigned_at')
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single delivery with order, order items, and customer loaded.
     */
    public function getDelivery(Delivery $delivery): Delivery
    {
        return $delivery->load(['order', 'order.items', 'order.user']);
    }

    /**
     * Assign a delivery man to an order.
     *
     * @throws DuplicateDeliveryException If order already has an active delivery
     */
    public function assignDelivery(Order $order, User $deliveryMan, User $admin): Delivery
    {
        if ($order->delivery()->exists()) {
            throw new DuplicateDeliveryException($order->order_number);
        }

        return Delivery::create([
            'order_id' => $order->id,
            'delivery_man_id' => $deliveryMan->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Mark delivery as preparing — transitions order to 'preparing'.
     */
    public function markPreparing(Delivery $delivery): Delivery
    {
        $this->orderService->transitionStatus($delivery->order, 'preparing');

        return $delivery->refresh();
    }

    /**
     * Mark delivery as picked up — transitions order to 'on_the_way', sets picked_up_at.
     */
    public function markPickedUp(Delivery $delivery): Delivery
    {
        $this->orderService->transitionStatus($delivery->order, 'on_the_way');

        $delivery->update(['picked_up_at' => now()]);

        return $delivery->refresh();
    }

    /**
     * Mark delivery as delivered — transitions order to 'delivered', sets delivered_at.
     */
    public function markDelivered(Delivery $delivery): Delivery
    {
        $this->orderService->transitionStatus($delivery->order, 'delivered');

        $delivery->update(['delivered_at' => now()]);

        return $delivery->refresh();
    }
}
