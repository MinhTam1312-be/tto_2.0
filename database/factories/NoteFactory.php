<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
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
            'title_note' => fake()->sentence(5),
            'content_note' => fake()->paragraph(),
            'cache_time_note' => fake()->numberBetween(1, 3600),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'document_id' => $document->id,
            'user_id' => $user->id,
        ];
    }
}
