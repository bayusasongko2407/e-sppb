<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowInstanceStepFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'workflow_instance_id' => WorkflowInstance::factory(),
            'workflow_step_id' => WorkflowStep::factory(),
            'sequence' => fake()->randomNumber(),
            'code' => fake()->regexify('[A-Za-z0-9]{30}'),
            'name' => fake()->name(),
            'approver_type' => fake()->regexify('[A-Za-z0-9]{30}'),
            'approval_mode' => fake()->regexify('[A-Za-z0-9]{20}'),
            'minimum_approvals' => fake()->randomNumber(),
            'sla_hours' => fake()->randomNumber(),
            'status' => fake()->regexify('[A-Za-z0-9]{30}'),
            'activated_at' => fake()->dateTime(),
            'due_at' => fake()->dateTime(),
            'acted_at' => fake()->dateTime(),
            'acted_by_id' => User::factory(),
            'remarks' => fake()->text(),
            'lock_version' => fake()->randomNumber(),
        ];
    }
}
