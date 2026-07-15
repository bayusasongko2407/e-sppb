<?php

namespace Database\Factories;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'code' => $this->faker->regexify('[A-Za-z0-9]{20}'),
            'name' => $this->faker->name(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
