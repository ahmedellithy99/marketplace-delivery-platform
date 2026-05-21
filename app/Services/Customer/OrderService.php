<?php

namespace App\Services\Customer;

use App\Events\OrderPlaced;
use App\Exceptions\ProductUnavailableException;
use App\Models\Order;
use App\Models\User;
use App\Services\Customer\CartService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        private CartService $cartService,
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

        // Calculate subtotal from cart items
        $subtotal = $cart->items->sum('total_price');

        // Total = subtotal (delivery fee set by admin when accepting)
        $total = $subtotal;

        // Generate unique order number
        $orderNumber = $this->generateOrderNumber();

        // Wrap in transaction
        $order = DB::transaction(function () use ($user, $data, $cart, $subtotal, $total, $orderNumber) {
            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'delivery_address' => $data['delivery_address'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee_min' => 0,
                'delivery_fee_max' => 0,
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
                    'variant_name' => $cartItem->variant?->name,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'discount_amount' => 0,
                    'total' => $cartItem->total_price,
                ]);
            }

            // Clear the cart
            $this->cartService->clearCart($cart);

            return $order;
        });

        event(new OrderPlaced($order));

        return $order->load('items');
    }

    /**
     * Get paginated order history for a user.
     * By default excludes delivered orders unless show_all is passed.
     */
    public function getOrders(User $user, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::where('user_id', $user->id)
            ->withCount('items');

        // Exclude delivered unless show_all is requested
        if (!$request->boolean('show_all')) {
            $query->where('status', '!=', 'delivered');
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());
    }

    /**
     * Get a single order with items (grouped by store) and delivery loaded.
     */
    public function getOrder(Order $order): Order
    {
        return $order->load(['items.store', 'delivery']);
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
}
