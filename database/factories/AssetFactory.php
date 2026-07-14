<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'location_id' => Location::factory(),
            'asset_location_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'asset_location_address' => fake()->text(),
            'barcode' => fake()->regexify('[A-Za-z0-9]{100}'),
            'condition' => fake()->regexify('[A-Za-z0-9]{20}'),
            'status' => fake()->regexify('[A-Za-z0-9]{20}'),
            'notes' => fake()->text(),
            'is_active' => fake()->boolean(),
        ];
    }
}
