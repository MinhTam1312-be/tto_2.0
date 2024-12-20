<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chapter>
 */
class ChapterFactory extends Factory
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
            'name_chapter' => fake()->sentence(5),
            'serial_chapter' => fake()->numberBetween(1, 100),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'course_id' => $course->id,
        ];
    }
}
