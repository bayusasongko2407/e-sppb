<?php

namespace Database\Factories;

use App\Models\DocumentGeneration;
use App\Models\DocumentPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentValidationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'document_generation_id' => DocumentGeneration::factory(),
            'document_page_id' => DocumentPage::factory(),
            'actor_id' => User::factory(),
            'validation_result' => fake()->regexify('[A-Za-z0-9]{20}'),
            'verification_channel' => fake()->regexify('[A-Za-z0-9]{20}'),
            'lookup_fingerprint_sha256' => fake()->randomLetter(),
            'request_fingerprint_sha256' => fake()->randomLetter(),
            'ip_address_hash_sha256' => fake()->randomLetter(),
            'user_agent_hash_sha256' => fake()->randomLetter(),
            'correlation_id' => Str::uuid(),
            'verified_at' => fake()->dateTime(),
            'metadata' => '{}',
        ];
    }
}
