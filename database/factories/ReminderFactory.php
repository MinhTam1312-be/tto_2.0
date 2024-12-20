<?php

namespace Database\Factories;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reminder>
 */
class ReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $enrollment = Enrollment::factory()->create();
        return [
            'id' => (string) Str::ulid(),
            'day_of_week' => fake()->randomElement(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            'time' => fake()->time(),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'enrollment_id' => $enrollment->id,
        ];
    }
}
