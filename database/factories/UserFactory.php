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
        'username' => fake()->userName(),
        'pin' => fake()->optional()->numberBetween(1000, 9999),
        'fullname' => fake()->name(),
        'age' => fake()->optional()->date('Y-m-d', '-18 years'),
        'email' => fake()->unique()->safeEmail(),
        'phonenumber' => fake()->unique()->numerify('##########'),
        'avatar' => fake()->imageUrl(200, 200, 'people'),
        'provider_id' => fake()->optional()->uuid(),
        'role' => fake()->randomElement(['client','marketing', 'instructor', 'accountant', 'admin']),
        'del_flag' => fake()->boolean(),
    ];
}

}
