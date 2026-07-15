<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Plant;
use App\Models\Unit;
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
            'asset_name' => $this->faker->words(3, true),
            'asset_location_data' => $this->faker->address(),
            'barcode' => $this->faker->ean13(),
            'condition' => 'GOOD',
            'status' => 'AVAILABLE',
            'unit_id' => Unit::factory(),
            'notes' => $this->faker->text(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
