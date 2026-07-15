<?php

namespace Database\Factories;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataImportFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'command_uuid' => fake()->uuid(),
            'commit_command_uuid' => fake()->uuid(),
            'plant_id' => Plant::factory(),
            'requested_by_id' => User::factory(),
            'committed_by_id' => User::factory(),
            'import_type' => fake()->regexify('[A-Za-z0-9]{50}'),
            'schema_version' => fake()->randomNumber(),
            'status' => fake()->regexify('[A-Za-z0-9]{30}'),
            'scan_status' => fake()->regexify('[A-Za-z0-9]{20}'),
            'original_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'stored_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'disk' => fake()->regexify('[A-Za-z0-9]{50}'),
            'directory' => fake()->regexify('[A-Za-z0-9]{255}'),
            'path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'mime_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'extension' => fake()->regexify('[A-Za-z0-9]{20}'),
            'file_size' => fake()->randomNumber(),
            'checksum_sha256' => fake()->randomLetter(),
            'scope_snapshot' => '{}',
            'options' => '{}',
            'total_rows' => fake()->randomNumber(),
            'valid_rows' => fake()->randomNumber(),
            'invalid_rows' => fake()->randomNumber(),
            'processed_rows' => fake()->randomNumber(),
            'successful_rows' => fake()->randomNumber(),
            'failed_rows' => fake()->randomNumber(),
            'validation_report_disk' => fake()->regexify('[A-Za-z0-9]{50}'),
            'validation_report_path' => fake()->regexify('[A-Za-z0-9]{500}'),
            'validation_report_checksum_sha256' => fake()->randomLetter(),
            'validation_started_at' => fake()->dateTime(),
            'validated_at' => fake()->dateTime(),
            'processing_started_at' => fake()->dateTime(),
            'completed_at' => fake()->dateTime(),
            'expires_at' => fake()->dateTime(),
            'error_code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'error_message' => fake()->text(),
            'lock_version' => fake()->randomNumber(),
        ];
    }
}
