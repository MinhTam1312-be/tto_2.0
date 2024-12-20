<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post_Category;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categrory = Post_Category::factory()->create();
        $user = User::factory()->create();

        return [
            'id' => (string) Str::ulid(),
            'title_post' => fake()->sentence(6, true),
            'content_post' => fake()->paragraphs(3, true),
            'img_post' => fake()->imageUrl(640, 480),
            'views_post' => fake()->numberBetween(0, 5000000),
            'status_post' => fake()->randomElement(['confirming', 'success', 'failed']),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'user_id' => $user->id,
            'category_id' => $categrory->id,
        ];
    }
}
