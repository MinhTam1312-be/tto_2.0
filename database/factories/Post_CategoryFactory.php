<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post_Category>
 */
class Post_CategoryFactory extends Factory
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
            'name_category' => fake()->word,
            'tags' => fake()->word,
            'del_flag' => fake()->randomElement(['true', 'false']),
        ];
    }
}
