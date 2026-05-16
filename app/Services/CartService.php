<?php

namespace App\Services;

use App\Exceptions\ProductUnavailableException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

class CartService
{
    /**
     * Get or create the user's cart (enforces single cart per customer).
     */
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Get the user's cart with items loaded.
     */
    public function getCart(User $user): Cart
    {
        $cart = $this->getOrCreateCart($user);

        return $cart->load(['items.product', 'items.variant']);
    }

    /**
     * Add an item to the user's cart with price resolution.
     *
     * @throws ProductUnavailableException
     */
    public function addCartItem(User $user, Product $product, ?ProductVariant $variant, int $quantity): CartItem
    {
        if (!$product->is_available) {
            throw new ProductUnavailableException($product->name);
        }

        $cart = $this->getOrCreateCart($user);
        $unitPrice = $this->resolvePrice($product, $variant);

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
            'price' => $unitPrice * $quantity,
        ]);

        return $cartItem->load(['product', 'variant']);
    }

    /**
     * Update the quantity of a cart item and recalculate price.
     */
    public function updateCartItem(CartItem $item, int $quantity): CartItem
    {
        $unitPrice = $this->resolvePrice($item->product, $item->variant);

        $item->update([
            'quantity' => $quantity,
            'price' => $unitPrice * $quantity,
        ]);

        $item->refresh();

        return $item->load(['product', 'variant']);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeCartItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Clear all items from a cart.
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Resolve the unit price based on priority: variant price > discounted_price > base price.
     */
    public function resolvePrice(Product $product, ?ProductVariant $variant): float
    {
        if ($variant !== null) {
            return (float) $variant->price;
        }

        if ($product->discounted_price !== null) {
            return (float) $product->discounted_price;
        }

        return (float) $product->price;
    }
}
