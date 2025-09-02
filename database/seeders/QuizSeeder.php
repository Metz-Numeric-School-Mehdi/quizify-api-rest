<?php

namespace Database\Seeders;

use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Quiz::withoutSyncingToSearch(function () {
            Quiz::create([
                "title" => "Quiz Laravel",
                "slug" => "quiz-laravel",
                "description" => "Testez vos connaissances sur Laravel.",
                "is_public" => true,
                "level_id" => 1,
                "status" => "published",
                "user_id" => 1,
                "duration" => 30,
                "pass_score" => 70,
                "thumbnail" => null,
                "category_id" => 1,
            ]);
        });
    }
}
