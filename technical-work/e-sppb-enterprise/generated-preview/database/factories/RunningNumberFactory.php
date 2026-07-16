<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class RunningNumberFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'plant_id' => AppModelsPlant::factory(),
            'document_type' => fake()->regexify('[A-Za-z0-9]{30}'),
            'period_key' => fake()->regexify('[A-Za-z0-9]{12}'),
            'prefix' => fake()->regexify('[A-Za-z0-9]{30}'),
            'digits' => fake()->randomDigitNotNull(),
            'last_number' => fake()->randomNumber(),
            'lock_version' => fake()->randomNumber(),
            'is_active' => fake()->boolean(),
        ];
    }
}
