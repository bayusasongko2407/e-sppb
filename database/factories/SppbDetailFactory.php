<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Item;
use App\Models\SppbHeader;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class SppbDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sppb_header_id' => SppbHeader::factory(),
            'line_no' => fake()->randomNumber(),
            'barcode_confirmed' => fake()->boolean(),
            'item_id' => Item::factory(),
            'asset_id' => Asset::factory(),
            'reference_code' => fake()->regexify('[A-Za-z0-9]{100}'),
            'is_from_master' => fake()->boolean(),
            'item_asset_name' => fake()->regexify('[A-Za-z0-9]{200}'),
            'unit_id' => Unit::factory(),
            'quantity' => fake()->randomFloat(2, 0, 9999999999999999.99),
            'remarks' => fake()->text(),
            'delivery_status' => fake()->regexify('[A-Za-z0-9]{20}'),
        ];
    }
}
