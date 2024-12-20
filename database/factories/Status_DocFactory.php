<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Status_Doc>
 */
class Status_DocFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $document = Document::factory()->create();
        $enrollment = Enrollment::factory()->create();
        
        return [
            'id' => (string) Str::ulid(),
            'status_doc' => fake()->boolean(0),
            'cache_time_video' => fake()->numberBetween(1, 3600),
            'del_flag' => fake()->randomElement(['true', 'false']),
            'document_id' => $document->id,
            'enrollment_id' => $enrollment->id,
        ];
    }
}
