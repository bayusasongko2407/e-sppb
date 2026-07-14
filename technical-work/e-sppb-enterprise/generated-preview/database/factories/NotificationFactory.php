<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_type' => fake()->regexify('[A-Za-z0-9]{30}'),
            'title' => fake()->sentence(4),
            'message' => fake()->text(),
            'url' => fake()->url(),
            'is_read' => fake()->boolean(),
            'read_at' => fake()->dateTime(),
        ];
    }
}
