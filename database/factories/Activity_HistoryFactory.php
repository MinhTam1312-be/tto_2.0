<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity_History>
 */
class Activity_HistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        return [
            'id' => (string) Str::ulid(),
            'name_activity' => fake()->sentence(5),
            'discription_activity' => fake()->sentence(5),
            'status_activity' => fake()->randomElement(['confirming', 'success', 'fail']),
            'user_id' => $user->id,
        ];
    }
}
