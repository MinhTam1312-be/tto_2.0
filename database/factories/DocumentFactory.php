<?php

namespace Database\Factories;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $chapter = Chapter::factory()->create();
        return [
            'id' => (string) Str::ulid(),
            'name_document' => fake()->sentence(5),
            'discription_document' => fake()->paragraph,
            // 'poster_document' => fake()->imageUrl(640, 480, 'education'),
            'url_video' => fake()->url(),
            'serial_document' => fake()->numberBetween(1, 100),
            'type_document' => fake()->randomElement(['video', 'code', 'quiz']),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'chapter_id' => $chapter->id,
        ];
    }
}
