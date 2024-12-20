<?php

namespace Database\Factories;

use App\Models\Comment_Document;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment_Document>
 */
class Comment_DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $document = Document::factory()->create();
        $user = User::factory()->create();
        return [
            'id' => (string) Str::ulid(),
            'comment_title' => fake() -> sentence(),
            'comment_text' => fake() ->paragraph(),
            'document_id' => $document->id,
            'user_id' => $user->id,
            'del_flag' => fake()->randomElement(['true', 'false']),
            'comment_to' => fake()->randomElement([null, Comment_Document::factory()]),
        ];
    }
}
