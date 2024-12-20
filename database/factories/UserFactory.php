<?php

namespace Database\Factories;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'id' => (string) Str::ulid(),
        'discription_user' => fake()->text(150),
        'password' => Hash::make('password'),
        'fullname' => fake()->name,
        'age' => fake()->numberBetween(18, 60),
        'email' => fake()->safeEmail,
        'avatar' => fake()->imageUrl(200, 200, 'people'),
        'phonenumber' => fake()->numerify('##########'),
        'provider_id' => fake()->uuid                   ,
        'role' => fake()->randomElement(['client','marketing', 'instructor', 'accountant', 'admin']),
        'del_flag' => fake()->randomElement(['true', 'false']),
        ];
    }
}
