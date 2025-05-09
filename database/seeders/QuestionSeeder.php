<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Question 1
        Question::create([
            'quiz_id' => 1,
            'content' => 'Quelle commande permet de créer un contrôleur dans Laravel ?',
            'question_type_id' => 1,
        ]);
        // Question 2
        Question::create([
            'quiz_id' => 1,
            'content' => 'Quel fichier contient la configuration de la base de données ?',
            'question_type_id' => 1,
        ]);
    }
}
