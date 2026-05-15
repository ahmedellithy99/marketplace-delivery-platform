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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->randomFloat(2, 2, 50);

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'quantity' => $quantity,
            'price' => $price,
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
            'price' => $variant->price,
        ]);
    }
}
