<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Exercise;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Code>
 */
class CodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $document = Document::factory()->create();
        
        return [
            'id' => (string) Str::ulid(),
            'question_code' => fake()->text(100),
            'answer_code' => fake()->text(100),
            'tutorial_code' => fake()->text(200),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'document_id' => $document->id,
        ];
    }
}
