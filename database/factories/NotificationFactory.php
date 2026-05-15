<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement([
                'order_placed', 'order_accepted', 'order_preparing',
                'order_on_the_way', 'order_delivered', 'order_cancelled',
                'delivery_assigned', 'new_order',
            ]),
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(10),
            'is_read' => false,
        ];
    }

    /**
     * Mark the notification as read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }
}
