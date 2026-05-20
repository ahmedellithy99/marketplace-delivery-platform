<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 5, 50);

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
        ];
    }

    /**
     * Associate a variant with the cart item.
     */
    public function withVariant(ProductVariant $variant): static
    {
        return $this->state(fn (array $attributes) => [
            'variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'unit_price' => $variant->price,
            'total_price' => $variant->price * ($attributes['quantity'] ?? 1),
        ]);
    }
}
