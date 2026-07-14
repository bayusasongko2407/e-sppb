<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'company_id' => AppModelsCompany::factory(),
            'plant_id' => AppModelsPlant::factory(),
            'location_id' => AppModelsocation::factory(),
            'barcode' => fake()->regexify('[A-Za-z0-9]{100}'),
            'serial_number' => fake()->regexify('[A-Za-z0-9]{100}'),
            'condition' => fake()->regexify('[A-Za-z0-9]{20}'),
            'status' => fake()->regexify('[A-Za-z0-9]{20}'),
            'notes' => fake()->text(),
            'is_active' => fake()->boolean(),
        ];
    }
}
