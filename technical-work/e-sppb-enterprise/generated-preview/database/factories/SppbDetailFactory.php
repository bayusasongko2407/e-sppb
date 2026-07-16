<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SppbHeader;
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
            'item_id' => AppModelsItem::factory(),
            'asset_id' => AppModelsAsset::factory(),
            'item_type' => fake()->regexify('[A-Za-z0-9]{20}'),
            'item_code' => fake()->regexify('[A-Za-z0-9]{30}'),
            'item_name' => fake()->regexify('[A-Za-z0-9]{200}'),
            'specification' => fake()->text(),
            'barcode' => fake()->regexify('[A-Za-z0-9]{100}'),
            'unit_id' => AppModelsNIT::FACTORY(),
            'unit_name' => fake()->regexify('[A-Za-z0-9]{100}'),
            'quantity' => fake()->randomFloat(2, 0, 9999999999999999.99),
            'approved_quantity' => fake()->randomFloat(2, 0, 9999999999999999.99),
            'released_quantity' => fake()->randomFloat(2, 0, 9999999999999999.99),
            'remarks' => fake()->text(),
        ];
    }
}
