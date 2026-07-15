<?php

namespace Database\Factories;

use App\Models\DocumentGeneration;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentPageFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'document_generation_id' => DocumentGeneration::factory(),
            'verification_uuid' => fake()->uuid(),
            'page_number' => fake()->randomNumber(),
            'page_checksum_sha256' => fake()->randomLetter(),
            'qr_payload_checksum_sha256' => fake()->randomLetter(),
            'verification_token_hash' => fake()->randomLetter(),
        ];
    }
}
