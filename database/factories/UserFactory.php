<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'department_id' => Department::factory(),
            'manager_id' => null,
            'nik' => fake()->unique()->numerify('NIK########'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => fake()->dateTime(),
            'password' => static::$password ??= \Illuminate\Support\Facades\Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
            'last_login_at' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ];
    }
}
