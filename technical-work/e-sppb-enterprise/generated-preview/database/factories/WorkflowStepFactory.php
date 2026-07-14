<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkflowTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowStepFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'workflow_template_id' => WorkflowTemplate::factory(),
            'sequence' => fake()->randomNumber(),
            'code' => fake()->regexify('[A-Za-z0-9]{30}'),
            'name' => fake()->name(),
            'approver_type' => fake()->regexify('[A-Za-z0-9]{30}'),
            'approver_user_id' => AppModelsSER::FACTORY(),
            'approver_position_id' => AppModelsPosition::factory(),
            'approver_role' => fake()->regexify('[A-Za-z0-9]{100}'),
            'approval_mode' => fake()->regexify('[A-Za-z0-9]{20}'),
            'minimum_approvals' => fake()->randomNumber(),
            'sla_hours' => fake()->randomNumber(),
            'allow_self_approval' => fake()->boolean(),
            'is_final' => fake()->boolean(),
            'configuration' => '{}',
        ];
    }
}
