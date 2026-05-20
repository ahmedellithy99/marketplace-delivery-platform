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
    public function definition(): array
    {
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
            'type' => 'simple',
            'base_price' => fake()->randomFloat(2, 5, 100),
            'is_available' => true,
        ];
    }

    /**
     * Create a variant-type product (no base_price).
     */
    public function variant(): static
    {
        return $this->state(fn () => [
            'type' => 'variant',
            'base_price' => null,
        ]);
    }

    /**
     * Create a measured-type product.
     */
    public function measured(): static
    {
        return $this->state(fn () => [
            'type' => 'measured',
            'base_price' => fake()->randomFloat(2, 50, 500),
            'measurement_unit' => fake()->randomElement(['kg', 'g', 'liter', 'piece']),
            'min_quantity' => 0.25,
            'max_quantity' => 10,
            'quantity_step' => 0.25,
        ]);
    }

    /**
     * Mark the product as unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn () => [
            'is_available' => false,
        ]);
    }
}
