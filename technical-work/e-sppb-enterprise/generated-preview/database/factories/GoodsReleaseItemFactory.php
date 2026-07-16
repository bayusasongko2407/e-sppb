<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GoodsRelease;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReleaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'goods_release_id' => GoodsRelease::factory(),
            'sppb_detail_id' => AppModelsSppbDetail::factory(),
            'quantity_requested' => fake()->randomFloat(2, 0, 9999999999999999.99),
            'quantity_released' => fake()->randomFloat(2, 0, 9999999999999999.99),
            'quantity_received' => fake()->randomFloat(2, 0, 9999999999999999.99),
            'condition_on_release' => fake()->regexify('[A-Za-z0-9]{20}'),
            'condition_on_receipt' => fake()->regexify('[A-Za-z0-9]{20}'),
            'is_checked' => fake()->boolean(),
            'notes' => fake()->text(),
        ];
    }
}
