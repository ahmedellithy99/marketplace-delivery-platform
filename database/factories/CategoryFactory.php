<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake()->randomElement([
                'Beverages', 'Snacks', 'Dairy', 'Bakery', 'Fruits',
                'Vegetables', 'Meat', 'Seafood', 'Frozen', 'Desserts',
                'Appetizers', 'Main Course', 'Salads', 'Pasta', 'Pizza',
                'Hot Drinks', 'Cold Drinks', 'Smoothies', 'Pastries',
                'Painkillers', 'Vitamins', 'Skincare', 'First Aid',
            ]),
        ];
    }

    /**
     * Set the category as a child of another category.
     */
    public function child(Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
