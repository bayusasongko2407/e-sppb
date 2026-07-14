<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SppbHeader;
use Illuminate\Database\Eloquent\Factories\Factory;

class SppbStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sppb_header_id' => SppbHeader::factory(),
            'workflow_instance_id' => AppModelsWorkflowInstance::factory(),
            'workflow_instance_step_id' => AppModelsWorkflowInstanceStep::factory(),
            'actor_id' => AppModelsSER::FACTORY(),
            'command_uuid' => fake()->uuid(),
            'action' => fake()->regexify('[A-Za-z0-9]{40}'),
            'from_status' => fake()->regexify('[A-Za-z0-9]{30}'),
            'to_status' => fake()->regexify('[A-Za-z0-9]{30}'),
            'remarks' => fake()->text(),
            'metadata' => '{}',
            'correlation_id' => fake()->uuid(),
            'logged_at' => fake()->dateTime(),
        ];
    }
}
