<?php

namespace App\Services;

use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\ProductUnavailableException;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

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
    public function __construct(
        private CartService $cartService,
        private DeliveryFeeService $deliveryFeeService,
    ) {}

    /**
     * Place a new order from the user's cart.
     *
     * @param User $user The customer placing the order
     * @param array $data Order data: delivery_address, latitude, longitude, notes (optional)
     * @return Order The created order with items loaded
     *
     * @throws InvalidArgumentException If cart is empty
     * @throws ProductUnavailableException If any product became unavailable
     */
    public function placeOrder(User $user, array $data): Order
    {
        $cart = $this->cartService->getCart($user);

        // Validate cart is not empty
        if ($cart->items->isEmpty()) {
            throw new InvalidArgumentException('Cannot place an order with an empty cart.');
        }

        // Validate all products are still available
        $unavailableProducts = $cart->items
            ->filter(fn ($item) => !$item->product->is_available)
            ->map(fn ($item) => $item->product->name)
            ->values()
            ->toArray();

        if (!empty($unavailableProducts)) {
            throw new ProductUnavailableException($unavailableProducts);
        }

        // Calculate delivery fee range
        $feeRange = $this->deliveryFeeService->calculateFeeRange(
            (float) $data['latitude'],
            (float) $data['longitude']
        );

        // Calculate subtotal from cart items
        $subtotal = $cart->items->sum('price');

        // Total = subtotal + fee_max (estimated total)
        $total = $subtotal + $feeRange['max'];

        // Generate unique order number
        $orderNumber = $this->generateOrderNumber();

        // Wrap in transaction
        $order = DB::transaction(function () use ($user, $data, $cart, $feeRange, $subtotal, $total, $orderNumber) {
            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'delivery_address' => $data['delivery_address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee_min' => $feeRange['min'],
                'delivery_fee_max' => $feeRange['max'],
                'delivery_fee' => null,
                'total' => $total,
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'store_id' => $cartItem->product->store_id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'product_name' => $cartItem->product->name,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price / $cartItem->quantity, // unit price
                    'total' => $cartItem->price, // total for this line item
                ]);
            }

            // Clear the cart
            $this->cartService->clearCart($cart);

            return $order;
        });

        return $order->load('items');
    }

    /**
     * Get paginated order history for a user.
     */
    public function getOrders(User $user, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single order with items and delivery loaded.
     */
    public function getOrder(Order $order): Order
    {
        return $order->load(['items', 'delivery']);
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
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

        return $order->refresh();
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
}
