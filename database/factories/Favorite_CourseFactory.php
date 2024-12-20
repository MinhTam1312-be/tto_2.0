<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite_Course>
 */
class Favorite_CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();

        return [
            'id' => (string) Str::ulid(),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'user_id' =>$user->id,
            'course_id' => $course->id,
        ];
    }
}
