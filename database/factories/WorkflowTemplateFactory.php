<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'code' => fake()->regexify('[A-Za-z0-9]{30}'),
            'name' => fake()->name(),
            'version' => fake()->randomNumber(),
            'plant_id' => Plant::factory(),
            'department_id' => Department::factory(),
            'document_type' => fake()->regexify('[A-Za-z0-9]{30}'),
            'description' => fake()->text(),
            'is_active' => fake()->boolean(),
            'effective_from' => fake()->dateTime(),
            'effective_until' => fake()->dateTime(),
        ];
    }
}
