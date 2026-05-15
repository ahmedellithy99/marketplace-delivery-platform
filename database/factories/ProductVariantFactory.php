<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement([
                'Small', 'Medium', 'Large', 'Extra Large',
                '250ml', '500ml', '1L',
                '250g', '500g', '1kg',
                'Regular', 'Family Size',
            ]),
            'price' => fake()->randomFloat(2, 2, 80),
        ];
    }
}
