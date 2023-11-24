<?php

namespace PERP\User\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use PERP\User\Models\User;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address_line' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => 'QLD',
            'post_code' => fake()->postcode(),
            'country' => 'AU',
            'date_of_birth' => fake()->date(),
            'mobile_number' => fake()->phoneNumber(),
        ];
    }
}
