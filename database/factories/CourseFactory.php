<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
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
        'name_course' => fake()->sentence(4),
        'img_course' => fake()->imageUrl(640, 480, 'education'),
        'discription_course' => fake()->text(100), // mô tả khóa học
        'status_course' => fake()->randomElement(['confirming', 'success', 'failed']), // confirming sau khi giảng viên thêm, success quảng lý khóa học chấp thuận lên admin, accept sau khi admin chấp nhận và công bố, failed khi khóa bị từ chối
        'price_course' => fake()->numberBetween(100000, 5000000),
        'discount_price_course' => fake()->optional()->numberBetween(0, 100),
        'views_course' => fake()->numberBetween(0, 10000),
        'rating_course' => fake()->randomFloat(1, 1, 5),
        'tax_rate' => fake()->randomFloat(1, 0, 100),
        'del_flag' => fake()->randomElement(['true', 'false']),
        'user_id' => $user->id,
        ];
    }
}
