<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(5)->create();
        \App\Models\Route::factory(5)->create();
        \App\Models\Course::factory(5)->create();
        \App\Models\FAQ_Course::factory(5)->create();
        \App\Models\Favorite_Course::factory(5)->create();
        \App\Models\Module::factory(5)->create();
        \App\Models\Enrollment::factory(5)->create();
        \App\Models\Reminder::factory(5)->create();
        \App\Models\Chapter::factory(5)->create();
        \App\Models\Document::factory(5)->create();
        \App\Models\Note::factory(5)->create();
        \App\Models\Status_Doc::factory(5)->create();
        \App\Models\Comment_Document::factory(5)->create();
        \App\Models\Question::factory(5)->create();
        \App\Models\Code::factory(5)->create();
        \App\Models\Transaction::factory(5)->create();
        \App\Models\Post_Category::factory(5)->create();
        \App\Models\Post::factory(5)->create();
        \App\Models\Comment_Post::factory(5)->create();
        \App\Models\Activity_History::factory(5)->create();
    }
}
