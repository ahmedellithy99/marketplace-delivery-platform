<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 500);
        $deliveryFeeMin = fake()->randomFloat(2, 2, 10);
        $deliveryFeeMax = fake()->randomFloat(2, $deliveryFeeMin + 1, $deliveryFeeMin + 10);
        $total = round($subtotal + $deliveryFeeMax, 2);

        return [
            'user_id' => User::factory()->customer(),
            'order_number' => 'ORD-' . fake()->unique()->numerify('######'),
            'status' => 'pending',
            'delivery_address' => fake()->address(),
            'latitude' => fake()->latitude(30, 37),
            'longitude' => fake()->longitude(35, 45),
            'notes' => fake()->optional(0.3)->sentence(),
            'subtotal' => $subtotal,
            'delivery_fee_min' => $deliveryFeeMin,
            'delivery_fee_max' => $deliveryFeeMax,
            'delivery_fee' => null,
            'total' => $total,
        ];
    }

    /**
     * Set the order status to accepted with a delivery fee.
     */
    public function accepted(): static
    {
        return $this->state(function (array $attributes) {
            $subtotal = $attributes['subtotal'] ?? fake()->randomFloat(2, 10, 500);
            $deliveryFeeMin = $attributes['delivery_fee_min'] ?? fake()->randomFloat(2, 2, 10);
            $deliveryFeeMax = $attributes['delivery_fee_max'] ?? $deliveryFeeMin + 5;
            $deliveryFee = fake()->randomFloat(2, $deliveryFeeMin, $deliveryFeeMax);

            return [
                'status' => 'accepted',
                'delivery_fee' => $deliveryFee,
                'total' => round($subtotal + $deliveryFee, 2),
            ];
        });
    }

    /**
     * Set the order status to preparing.
     */
    public function preparing(): static
    {
        return $this->accepted()->state(fn (array $attributes) => [
            'status' => 'preparing',
        ]);
    }

    /**
     * Set the order status to on_the_way.
     */
    public function onTheWay(): static
    {
        return $this->accepted()->state(fn (array $attributes) => [
            'status' => 'on_the_way',
        ]);
    }

    /**
     * Set the order status to delivered.
     */
    public function delivered(): static
    {
        return $this->accepted()->state(fn (array $attributes) => [
            'status' => 'delivered',
        ]);
    }

    /**
     * Set the order status to cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
