<?php

namespace Database\Seeders;

use App\Models\Answer;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    public function run(): void
    {
        // Question 1
        Answer::create([
            'question_id' => 1,
            'content' => 'php artisan make:controller',
            'is_correct' => true,
        ]);
        Answer::create([
            'question_id' => 1,
            'content' => 'php artisan make:model',
            'is_correct' => false,
        ]);
        Answer::create([
            'question_id' => 1,
            'content' => 'php artisan make:migration',
            'is_correct' => false,
        ]);

        // Question 2
        Answer::create([
            'question_id' => 2,
            'content' => '.env',
            'is_correct' => true,
        ]);
        Answer::create([
            'question_id' => 2,
            'content' => 'routes/web.php',
            'is_correct' => false,
        ]);
        Answer::create([
            'question_id' => 2,
            'content' => 'config/app.php',
            'is_correct' => false,
        ]);
    }
}
