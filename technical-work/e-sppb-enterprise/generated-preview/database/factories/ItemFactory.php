<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => fake()->regexify('[A-Za-z0-9]{30}'),
            'name' => fake()->name(),
            'specification' => fake()->text(),
            'unit_id' => Unit::factory(),
            'item_type' => fake()->regexify('[A-Za-z0-9]{20}'),
            'is_active' => fake()->boolean(),
        ];
    }
}
