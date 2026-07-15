<?php

namespace Database\Factories;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'name' => $this->faker->name(),
            'address' => $this->faker->text(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
