<?php

namespace Database\Factories;

use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'sppb_header_id' => SppbHeader::factory(),
            'original_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'stored_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'disk' => fake()->regexify('[A-Za-z0-9]{50}'),
            'directory' => fake()->regexify('[A-Za-z0-9]{255}'),
            'path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'mime_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'extension' => fake()->regexify('[A-Za-z0-9]{20}'),
            'file_size' => fake()->randomNumber(),
            'checksum_sha256' => fake()->randomLetter(),
            'uploaded_by' => User::factory(),
            'scan_status' => fake()->regexify('[A-Za-z0-9]{20}'),
            'uploader_id' => User::factory(),
        ];
    }
}
