<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'module' => fake()->regexify('[A-Za-z0-9]{50}'),
            'action' => fake()->regexify('[A-Za-z0-9]{50}'),
            'subject_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'subject_id' => fake()->randomNumber(),
            'description' => fake()->text(),
            'old_values' => '{}',
            'new_values' => '{}',
            'ip_address' => fake()->regexify('[A-Za-z0-9]{45}'),
            'user_agent' => fake()->text(),
            'correlation_id' => fake()->uuid(),
        ];
    }
}
