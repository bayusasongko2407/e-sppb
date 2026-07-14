<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReleaseFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'release_number' => fake()->regexify('[A-Za-z0-9]{50}'),
            'sppb_header_id' => SppbHeader::factory(),
            'release_sequence' => fake()->randomNumber(),
            'created_by' => User::factory(),
            'sender_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'sender_address' => fake()->text(),
            'receiver_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'receiver_address' => fake()->text(),
            'sender_user_id' => User::factory(),
            'receiver_user_id' => User::factory(),
            'driver_name' => fake()->regexify('[A-Za-z0-9]{100}'),
            'vehicle_number' => fake()->regexify('[A-Za-z0-9]{50}'),
            'expedition_name' => fake()->regexify('[A-Za-z0-9]{100}'),
            'delivery_date' => fake()->date(),
            'received_at' => fake()->dateTime(),
            'received_by' => User::factory(),
            'status' => fake()->regexify('[A-Za-z0-9]{20}'),
            'notes' => fake()->text(),
            'verification_hash' => fake()->randomLetter(),
        ];
    }
}
