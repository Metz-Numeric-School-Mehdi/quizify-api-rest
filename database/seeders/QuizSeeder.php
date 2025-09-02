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

            Quiz::create([
                "title" => "Quiz PHP Avancé",
                "slug" => "quiz-php-avance",
                "description" => "Questions avancées sur PHP et la programmation orientée objet.",
                "is_public" => true,
                "level_id" => 2,
                "status" => "published",
                "user_id" => 1,
                "duration" => 45,
                "pass_score" => 75,
                "thumbnail" => null,
                "category_id" => 1,
            ]);

            Quiz::create([
                "title" => "Quiz JavaScript ES6+",
                "slug" => "quiz-javascript-es6",
                "description" => "Maîtrisez les nouvelles fonctionnalités de JavaScript ES6 et plus.",
                "is_public" => true,
                "level_id" => 2,
                "status" => "published",
                "user_id" => 1,
                "duration" => 40,
                "pass_score" => 70,
                "thumbnail" => null,
                "category_id" => 1,
            ]);

            Quiz::create([
                "title" => "Quiz Base de Données",
                "slug" => "quiz-base-donnees",
                "description" => "Questions sur MySQL, PostgreSQL et les concepts de base de données.",
                "is_public" => false,
                "level_id" => 1,
                "status" => "draft",
                "user_id" => 1,
                "duration" => 35,
                "pass_score" => 65,
                "thumbnail" => null,
                "category_id" => 1,
            ]);

            Quiz::create([
                "title" => "Quiz React Fundamentals",
                "slug" => "quiz-react-fundamentals",
                "description" => "Testez vos connaissances de base sur React et les composants.",
                "is_public" => true,
                "level_id" => 1,
                "status" => "published",
                "user_id" => 1,
                "duration" => 25,
                "pass_score" => 60,
                "thumbnail" => null,
                "category_id" => 1,
            ]);
        });
    }
}
