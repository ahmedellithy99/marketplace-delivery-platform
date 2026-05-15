<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->accepted(),
            'delivery_man_id' => User::factory()->delivery(),
            'assigned_by' => User::factory()->admin(),
            'assigned_at' => now(),
            'picked_up_at' => null,
            'delivered_at' => null,
        ];
    }

    /**
     * Mark the delivery as picked up.
     */
    public function pickedUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'picked_up_at' => now(),
        ]);
    }

    /**
     * Mark the delivery as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'picked_up_at' => now()->subMinutes(30),
            'delivered_at' => now(),
        ]);
    }
}
