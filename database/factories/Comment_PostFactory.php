<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment_Post;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment_Post>
 */
class Comment_PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        return [
            'id' => (string) Str::ulid(),
            'comment_text' => fake()->sentence(10, true),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'user_id' => $user->id,
            'post_id' => $post->id,
            'comment_to' => fake()->randomElement([null, Comment_Post::factory()]),
        ];
    }
}
