<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlantFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->regexify('[A-Za-z0-9]{20}'),
            'name' => $this->faker->name(),
            'address' => $this->faker->text(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
