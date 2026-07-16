<?php

namespace Database\Factories;

use App\Models\DocumentTemplate;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentGenerationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'command_uuid' => fake()->uuid(),
            'document_template_id' => DocumentTemplate::factory(),
            'template_version' => fake()->randomNumber(),
            'plant_id' => Plant::factory(),
            'sppb_header_id' => null,
            'goods_release_id' => null,
            'document_type' => fake()->regexify('[A-Za-z0-9]{30}'),
            'document_number' => fake()->regexify('[A-Za-z0-9]{100}'),
            'source_revision_no' => fake()->randomNumber(),
            'generation_no' => fake()->randomNumber(),
            'supersedes_id' => null,
            'generated_by_id' => User::factory(),
            'revoked_by_id' => null,
            'status' => fake()->regexify('[A-Za-z0-9]{30}'),
            'is_official' => fake()->boolean(),
            'plant_code_snapshot' => fake()->regexify('[A-Za-z0-9]{20}'),
            'plant_name_snapshot' => fake()->regexify('[A-Za-z0-9]{150}'),
            'render_payload' => [],
            'source_checksum_sha256' => fake()->randomLetter(),
            'disk' => fake()->regexify('[A-Za-z0-9]{50}'),
            'directory' => fake()->regexify('[A-Za-z0-9]{255}'),
            'stored_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'mime_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'file_size' => fake()->randomNumber(),
            'checksum_sha256' => fake()->randomLetter(),
            'page_count' => fake()->randomNumber(),
            'processing_started_at' => fake()->dateTime(),
            'generated_at' => fake()->dateTime(),
            'expires_at' => fake()->dateTime(),
            'revoked_at' => fake()->dateTime(),
            'revocation_reason' => fake()->text(),
            'error_code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'error_message' => fake()->text(),
            'lock_version' => fake()->randomNumber(),
        ];
    }
}
