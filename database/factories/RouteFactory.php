<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
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
            'name_route' => fake()->sentence(3),
            'img_route' => fake()->imageUrl(640, 480, 'education'),
            'discription_route' => fake()->paragraph,
            'del_flag' => fake()->randomElement(['true', 'false']),
        ];
    }
}
