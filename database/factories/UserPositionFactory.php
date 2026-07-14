<?php

namespace Database\Factories;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPositionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'position_id' => Position::factory(),
            'is_primary' => fake()->boolean(),
            'is_active' => fake()->boolean(),
            'valid_from' => fake()->dateTime(),
            'valid_until' => fake()->dateTime(),
        ];
    }
}
