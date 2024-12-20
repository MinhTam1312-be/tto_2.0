<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FAQ_Course>
 */
class FAQ_CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = Course::factory()->create();
        return [
            'id' => (string) Str::ulid(),
            'question_faq' => fake()->sentence(10),
            'answer_faq' => fake()->paragraph,
            'del_flag' => fake()->randomElement(['true', 'false']),
            'course_id' => $course->id,
        ];
    }
}
