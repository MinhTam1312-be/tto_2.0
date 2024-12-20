<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
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
            'rating_course' => fake()->optional()->numberBetween(1, 5),
            'feedback_text' => fake()->optional()->paragraph(),
            'status_course' => fake()->randomElement(['completed', 'in_progress', 'failed']),
            'certificate_course' => fake()->optional()->text(150),
            'enroll' => fake()->boolean(),
            'del_flag' => fake()->boolean(),
            'course_id' => $course->id,
            'user_id' => $user->id,
        ];
    }
    

    
}
