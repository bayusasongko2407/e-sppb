<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'plant_id' => AppModelsPlant::factory(),
            'department_id' => AppModelsDepartment::factory(),
            'manager_id' => AppModelsSER::FACTORY(),
            'nik' => fake()->regexify('[A-Za-z0-9]{30}'),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'email_verified_at' => fake()->dateTime(),
            'password' => fake()->password(),
            'remember_token' => Str::random(10),
            'is_active' => fake()->boolean(),
            'last_login_at' => fake()->dateTime(),
            'failed_login_attempts' => fake()->randomNumber(),
            'locked_until' => fake()->dateTime(),
        ];
    }
}
