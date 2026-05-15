<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 1, 100);

        return [
            'store_id' => Store::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->randomElement([
                'Fresh Milk', 'Whole Wheat Bread', 'Organic Eggs', 'Grilled Chicken',
                'Caesar Salad', 'Margherita Pizza', 'Espresso', 'Iced Latte',
                'Chocolate Cake', 'Orange Juice', 'Mineral Water', 'Paracetamol',
                'Vitamin C', 'Hand Sanitizer', 'Banana Smoothie', 'Club Sandwich',
                'Beef Burger', 'French Fries', 'Green Tea', 'Cheesecake',
            ]),
            'description' => fake()->optional(0.7)->sentence(),
            'price' => $price,
            'discounted_price' => null,
            'is_available' => true,
        ];
    }

    /**
     * Set a discounted price on the product.
     */
    public function discounted(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? fake()->randomFloat(2, 10, 100);
            $discount = fake()->randomFloat(2, 1, $price * 0.5);

            return [
                'price' => $price,
                'discounted_price' => round($price - $discount, 2),
            ];
        });
    }

    /**
     * Mark the product as unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
