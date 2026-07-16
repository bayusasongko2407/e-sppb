<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowCommandFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'command_uuid' => fake()->uuid(),
            'command_type' => fake()->regexify('[A-Za-z0-9]{50}'),
            'aggregate_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'aggregate_id' => fake()->randomNumber(),
            'actor_id' => User::factory(),
            'payload' => '{}',
            'status' => fake()->regexify('[A-Za-z0-9]{20}'),
            'attempts' => fake()->randomNumber(),
            'processed_at' => fake()->dateTime(),
            'error_code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'error_message' => fake()->text(),
        ];
    }
}
