<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'name' => fake()->name(),
            'document_type' => fake()->regexify('[A-Za-z0-9]{30}'),
            'version' => fake()->randomNumber(),
            'plant_id' => Plant::factory(),
            'renderer' => fake()->regexify('[A-Za-z0-9]{50}'),
            'template_path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'template_checksum_sha256' => fake()->randomLetter(),
            'configuration' => '{}',
            'description' => fake()->text(),
            'is_active' => fake()->boolean(),
            'effective_from' => fake()->dateTime(),
            'effective_until' => fake()->dateTime(),
            'created_by_id' => User::factory(),
        ];
    }
}
