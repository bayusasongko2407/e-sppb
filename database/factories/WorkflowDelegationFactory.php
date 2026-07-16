<?php

namespace Database\Factories;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowDelegationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'delegator_id' => User::factory(),
            'delegate_id' => User::factory(),
            'plant_id' => Plant::factory(),
            'starts_at' => fake()->dateTime(),
            'ends_at' => fake()->dateTime(),
            'reason' => fake()->text(),
            'is_active' => fake()->boolean(),
        ];
    }
}
