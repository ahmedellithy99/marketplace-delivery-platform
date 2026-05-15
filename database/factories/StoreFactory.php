<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openingHour = fake()->numberBetween(6, 10);
        $closingHour = fake()->numberBetween(20, 23);

        return [
            'name' => fake()->company(),
            'store_type_id' => StoreType::factory(),
            'phone' => fake()->unique()->numerify('09########'),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(30, 37),
            'longitude' => fake()->longitude(35, 45),
            'opening_time' => sprintf('%02d:00', $openingHour),
            'closing_time' => sprintf('%02d:00', $closingHour),
        ];
    }

    /**
     * Set the store type by name (finds or creates the StoreType).
     */
    public function ofType(string $typeName): static
    {
        return $this->state(fn (array $attributes) => [
            'store_type_id' => StoreType::firstOrCreate(['name' => $typeName])->id,
        ]);
    }

    /**
     * Set the store type to supermarket.
     */
    public function supermarket(): static
    {
        return $this->ofType('supermarket');
    }

    /**
     * Set the store type to restaurant.
     */
    public function restaurant(): static
    {
        return $this->ofType('restaurant');
    }

    /**
     * Set the store type to cafe.
     */
    public function cafe(): static
    {
        return $this->ofType('cafe');
    }

    /**
     * Set the store type to pharmacy.
     */
    public function pharmacy(): static
    {
        return $this->ofType('pharmacy');
    }
}
