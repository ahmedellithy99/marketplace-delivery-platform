<?php

namespace App\Services\Admin;

use App\Events\DeliveryAssigned;
use App\Events\OrderStatusChanged;
use App\Exceptions\DuplicateDeliveryException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Filters\Admin\OrderFilter;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Allowed status transitions.
     * 'cancelled' is reachable from any active status (admin only).
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['accepted', 'cancelled'],
        'accepted' => ['preparing', 'cancelled'],
        'preparing' => ['on_the_way', 'cancelled'],
        'on_the_way' => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
    ];

    /**
     * Get paginated orders for admin listing with filters.
     */
    public function getAdminOrders(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with('user')
            ->filter(new OrderFilter($request))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single order with items (grouped by store) and delivery loaded.
     */
    public function getOrder(Order $order): Order
    {
        return $order->load(['items.store', 'delivery.deliveryMan', 'user']);
    }

    /**
     * Get order items grouped by store with store details.
     *
     * Returns items grouped by store_id, each group containing:
     * - store: the Store model (name, address, phone for pickup)
     * - store_address: the store's address for delivery man pickup
     * - items: the order items belonging to that store
     *
     * A single delivery fee is calculated regardless of store count.
     */
    public function getOrderGroupedByStore(Order $order): array
    {
        $order->loadMissing(['items.product.store', 'items.store']);

        return $order->items
            ->groupBy('store_id')
            ->map(function ($items, $storeId) {
                $store = $items->first()->store;

                return [
                    'store' => $store,
                    'store_address' => $store?->address,
                    'items' => $items->values(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Accept a pending order by setting the delivery fee and recalculating total.
     *
     * @param Order $order The order to accept
     * @param float $deliveryFee The actual delivery fee
     * @return Order The refreshed order
     *
     * @throws InvalidStatusTransitionException If order is not in 'pending' status
     * @throws ValidationException If delivery_fee is missing or zero
     */
    public function acceptOrder(Order $order, float $deliveryFee): Order
    {
        if ($order->status !== 'pending') {
            throw new InvalidStatusTransitionException($order->status, 'accepted');
        }

        if ($deliveryFee <= 0) {
            throw ValidationException::withMessages([
                'delivery_fee' => ['The delivery fee is required and must be greater than zero.'],
            ]);
        }

        $order->update([
            'delivery_fee' => $deliveryFee,
            'total' => (float) $order->subtotal + $deliveryFee,
        ]);

        return $this->transitionStatus($order->refresh(), 'accepted');
    }

    /**
     * Cancel an order from any active status.
     *
     * @param Order $order The order to cancel
     * @return Order The refreshed order
     *
     * @throws InvalidStatusTransitionException If order is already delivered or cancelled
     */
    public function cancelOrder(Order $order): Order
    {
        return $this->transitionStatus($order, 'cancelled');
    }

    /**
     * Transition an order to a new status.
     *
     * @param Order $order The order to transition
     * @param string $newStatus The target status
     * @return Order The refreshed order
     *
     * @throws InvalidStatusTransitionException If the transition is not allowed
     */
    public function transitionStatus(Order $order, string $newStatus): Order
    {
        $currentStatus = $order->status;
        $allowedTargets = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (!in_array($newStatus, $allowedTargets, true)) {
            throw new InvalidStatusTransitionException($currentStatus, $newStatus);
        }

        $order->update(['status' => $newStatus]);

        event(new OrderStatusChanged($order, $currentStatus, $newStatus));

        return $order->refresh();
    }

    /**
     * Assign a delivery man to an order.
     *
     * @param Order $order The order to assign delivery to
     * @param User $deliveryMan The delivery man to assign
     * @param User $admin The admin performing the assignment
     * @return Delivery The created delivery record
     *
     * @throws DuplicateDeliveryException If order already has an active delivery
     */
    public function assignDelivery(Order $order, User $deliveryMan, User $admin): Delivery
    {
        // Check if order already has an active delivery
        if ($order->delivery()->exists()) {
            throw new DuplicateDeliveryException($order->order_number);
        }

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'delivery_man_id' => $deliveryMan->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        event(new DeliveryAssigned($delivery));

        return $delivery;
    }

    /**
     * Get delivery personnel with their active delivery counts.
     */
    public function getDeliveryPersonnel(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('role', 'delivery')
            ->withCount(['deliveries as active_deliveries_count' => function ($q) {
                $q->whereNull('delivered_at');
            }])
            ->get(['id', 'name', 'phone']);
    }
}
