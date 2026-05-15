<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
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
            'order_id' => Order::factory(),
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'product_name' => fake()->words(3, true),
            'quantity' => $quantity,
            'price' => $price,
            'total' => round($price * $quantity, 2),
        ];
    }
}
