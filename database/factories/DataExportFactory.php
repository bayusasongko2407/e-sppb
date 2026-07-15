<?php

namespace Database\Factories;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataExportFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'command_uuid' => fake()->uuid(),
            'plant_id' => Plant::factory(),
            'requested_by_id' => User::factory(),
            'export_type' => fake()->regexify('[A-Za-z0-9]{50}'),
            'dataset' => fake()->regexify('[A-Za-z0-9]{100}'),
            'schema_version' => fake()->randomNumber(),
            'format' => fake()->regexify('[A-Za-z0-9]{20}'),
            'status' => fake()->regexify('[A-Za-z0-9]{30}'),
            'scope_snapshot' => '{}',
            'filters' => '{}',
            'columns' => '{}',
            'options' => '{}',
            'disk' => fake()->regexify('[A-Za-z0-9]{50}'),
            'directory' => fake()->regexify('[A-Za-z0-9]{255}'),
            'stored_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'mime_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'file_size' => fake()->randomNumber(),
            'checksum_sha256' => fake()->randomLetter(),
            'total_rows' => fake()->randomNumber(),
            'processed_rows' => fake()->randomNumber(),
            'download_count' => fake()->randomNumber(),
            'processing_started_at' => fake()->dateTime(),
            'completed_at' => fake()->dateTime(),
            'expires_at' => fake()->dateTime(),
            'last_downloaded_at' => fake()->dateTime(),
            'error_code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'error_message' => fake()->text(),
            'lock_version' => fake()->randomNumber(),
        ];
    }
}
