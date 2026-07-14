<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkflowTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'workflow_template_id' => WorkflowTemplate::factory(),
            'sppb_header_id' => AppModelsSppbHeader::factory(),
            'template_version' => fake()->randomNumber(),
            'revision_no' => fake()->randomNumber(),
            'status' => fake()->regexify('[A-Za-z0-9]{30}'),
            'current_sequence' => fake()->randomNumber(),
            'started_at' => fake()->dateTime(),
            'finished_at' => fake()->dateTime(),
            'failure_code' => fake()->regexify('[A-Za-z0-9]{50}'),
            'failure_message' => fake()->text(),
        ];
    }
}
