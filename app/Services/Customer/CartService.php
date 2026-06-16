<?php

namespace App\Services\Customer;

use App\Exceptions\ProductUnavailableException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\PricingService;

class CartService
{
    public function __construct(
        protected PricingService $pricingService
    ) {}

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

        $cart->load(['items.product.media', 'items.product.store', 'items.product.variants', 'items.variant']);

        $this->pricingService->loadCollectionPricing($cart->items->pluck('product')->filter());
        foreach ($cart->items->pluck('variant')->filter() as $variant) {
            $this->pricingService->loadVariantPricing($variant);
        }

        return $cart;
    }

    /**
     * Add an item to the user's cart with price resolution via PricingService.
     *
     * @throws ProductUnavailableException
     */
    public function addCartItem(User $user, int $productId, ?int $variantId, float $quantity = 1): CartItem
    {
        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::findOrFail($variantId) : null;

        // Validate variant belongs to this product
        if ($variant && $variant->product_id !== $product->id) {
            abort(422, 'Variant does not belong to this product.');
        }

        if (!$product->is_available) {
            throw new ProductUnavailableException($product->name);
        }

        $cart = $this->getOrCreateCart($user);
        $priceResult = $this->pricingService->calculate($product, $variant, $quantity);

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $priceResult->effectivePrice,
            'total_price' => $priceResult->total,
        ]);

        return $cartItem->load(['product', 'variant']);
    }

    /**
     * Update the quantity of a cart item and recalculate price.
     */
    public function updateCartItem(CartItem $item, float $quantity): CartItem
    {
        $priceResult = $this->pricingService->calculate($item->product, $item->variant, $quantity);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $priceResult->effectivePrice,
            'total_price' => $priceResult->total,
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
}
