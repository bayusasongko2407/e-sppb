<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LegacyReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'source_system' => fake()->regexify('[A-Za-z0-9]{50}'),
            'source_table' => fake()->regexify('[A-Za-z0-9]{100}'),
            'legacy_id' => fake()->regexify('[A-Za-z0-9]{100}'),
            'target_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'target_id' => fake()->randomNumber(),
            'raw_hash' => fake()->randomLetter(),
            'migrated_at' => fake()->dateTime(),
        ];
    }
}
