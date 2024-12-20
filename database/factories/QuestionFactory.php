<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Exercise;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Tạo một Document trước
        $document = Document::factory()->create();
        return [
            // Sử dụng id của Document làm id cho Question
            'id' => $document->id,
            'content_question' => fake()->sentence(10),
            'answer_question' => fake()->sentence(5),
            'type_question' => fake()->randomElement(['multiple_choice', 'fill', 'true_false']), // Loại câu hỏi
            'del_flag' => fake()->boolean(), // true/false
        ];
    }
}
