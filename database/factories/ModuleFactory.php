<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Route;
use App\Models\Course;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $route = Route::factory()->create();
        $course = Course::factory()->create();

        return [
            'id' => (string) Str::ulid(),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'route_id' => $route->id,
            'course_id' => $course->id,
        ];
    }
}
