<?php

namespace Database\Factories;

use App\Models\StoreType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreType>
 */
class StoreTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'is_active' => true,
        ];
    }

    /**
     * Mark the type as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
