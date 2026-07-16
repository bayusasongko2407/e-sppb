<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SppbHeaderFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'document_number' => fake()->regexify('[A-Za-z0-9]{50}'),
            'company_id' => Company::factory(),
            'plant_id' => AppModelsPlant::factory(),
            'department_id' => AppModelsDepartment::factory(),
            'requester_id' => User::factory(),
            'origin_location_id' => AppModelsocation::factory(),
            'destination_location_id' => AppModelsocation::factory(),
            'project_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'request_date' => fake()->date(),
            'date_needed' => fake()->date(),
            'purpose' => fake()->text(),
            'is_urgent' => fake()->boolean(),
            'status' => fake()->regexify('[A-Za-z0-9]{30}'),
            'revision_no' => fake()->randomNumber(),
            'current_workflow_instance_id' => AppModelsWorkflowInstance::factory(),
            'current_step_sequence' => fake()->randomNumber(),
            'current_approver_id' => User::factory(),
            'lock_version' => fake()->randomNumber(),
            'submitted_at' => fake()->dateTime(),
            'approved_at' => fake()->dateTime(),
            'rejected_at' => fake()->dateTime(),
            'cancelled_at' => fake()->dateTime(),
            'completed_at' => fake()->dateTime(),
            'rejected_reason' => fake()->text(),
            'cancelled_reason' => fake()->text(),
        ];
    }
}
