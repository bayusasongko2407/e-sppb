<?php

namespace Database\Factories;

use App\Models\SppbHeader;
use App\Models\User;
use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
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
            'workflow_instance_id' => WorkflowInstance::factory(),
            'workflow_instance_step_id' => WorkflowInstanceStep::factory(),
            'actor_id' => User::factory(),
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
