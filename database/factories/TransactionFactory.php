<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'amount' => fake()->numberBetween(1000, 1000000),
            'payment_method' => fake()->randomElement(['Momo', 'VnPay']),
            'status' => fake()->randomElement(['pending', 'completed', 'failed', 'canceled']),
            'payment_discription' => fake()->text(200),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'enrollment_id' => $enrollment->id,
        ];
    }
}
