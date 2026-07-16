<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkflowInstanceStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowStepApproverFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'workflow_instance_step_id' => WorkflowInstanceStep::factory(),
            'approver_id' => User::factory(),
            'delegated_from_id' => User::factory(),
            'status' => fake()->regexify('[A-Za-z0-9]{20}'),
            'acted_at' => fake()->dateTime(),
            'remarks' => fake()->text(),
        ];
    }
}
