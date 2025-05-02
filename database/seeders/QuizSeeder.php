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
        $quizzes = [
            [
                "title" => "Introduction au PHP",
                "description" => "Testez vos connaissances de base sur le langage PHP.",
                "slug" => "introduction-au-php",
                "thumbnail" => "https://www.php.net/images/logos/php-logo.svg",
                "duration" => 45,
                "max_attempts" => 3,
                "pass_score" => 70,
                "is_public" => 1,
                "user_id" => 2,
                "category_id" => 1,
                "level_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "JavaScript Avancé",
                "description" =>
                    "Maîtrisez-vous les closures, promesses et async/await en JavaScript ?",
                "slug" => "javascript-avance",
                "thumbnail" =>
                    "https://upload.wikimedia.org/wikipedia/commons/6/6a/JavaScript-logo.png",
                "duration" => 60,
                "max_attempts" => 2,
                "pass_score" => 80,
                "is_public" => 1,
                "user_id" => 3,
                "category_id" => 1,
                "level_id" => 2,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "Culture Générale",
                "description" => "Un quiz pour tester vos connaissances générales sur le monde.",
                "slug" => "culture-generale",
                "thumbnail" => "https://images.unsplash.com/photo-1506744038136-46273834b3fb",
                "duration" => 30,
                "max_attempts" => 5,
                "pass_score" => 60,
                "is_public" => 1,
                "user_id" => 4,
                "category_id" => 2,
                "level_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "Bases de la langue française",
                "description" => "Évaluez votre vocabulaire et votre grammaire en français.",
                "slug" => "bases-langue-francaise",
                "thumbnail" =>
                    "https://upload.wikimedia.org/wikipedia/commons/c/c3/Flag_of_France.svg",
                "duration" => 25,
                "max_attempts" => 4,
                "pass_score" => 75,
                "is_public" => 0,
                "user_id" => 5,
                "category_id" => 3,
                "level_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "Mathématiques pour débutants",
                "description" => "Addition, soustraction, multiplication et division.",
                "slug" => "mathematiques-debutants",
                "thumbnail" => "https://images.unsplash.com/photo-1464983953574-0892a716854b",
                "duration" => 40,
                "max_attempts" => 3,
                "pass_score" => 65,
                "is_public" => 1,
                "user_id" => 6,
                "category_id" => 4,
                "level_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "Sciences de la vie",
                "description" => "Testez vos connaissances en biologie et en sciences naturelles.",
                "slug" => "sciences-de-la-vie",
                "thumbnail" => "https://images.unsplash.com/photo-1465101046530-73398c7f28ca",
                "duration" => 35,
                "max_attempts" => 3,
                "pass_score" => 70,
                "is_public" => 1,
                "user_id" => 2,
                "category_id" => 5,
                "level_id" => 2,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "Géographie du monde",
                "description" => "Connaissez-vous les capitales et les pays du monde ?",
                "slug" => "geographie-du-monde",
                "thumbnail" => "https://images.unsplash.com/photo-1502082553048-f009c37129b9",
                "duration" => 30,
                "max_attempts" => 4,
                "pass_score" => 70,
                "is_public" => 1,
                "user_id" => 3,
                "category_id" => 6,
                "level_id" => 2,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "Python pour les débutants",
                "description" => "Un quiz pour tester vos bases en Python.",
                "slug" => "python-debutants",
                "thumbnail" => "https://www.python.org/static/community_logos/python-logo.png",
                "duration" => 50,
                "max_attempts" => 3,
                "pass_score" => 75,
                "is_public" => 1,
                "user_id" => 4,
                "category_id" => 1,
                "level_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "Musique et instruments",
                "description" => "Testez vos connaissances sur la musique et les instruments.",
                "slug" => "musique-instruments",
                "thumbnail" => "https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4",
                "duration" => 30,
                "max_attempts" => 5,
                "pass_score" => 60,
                "is_public" => 0,
                "user_id" => 5,
                "category_id" => 7,
                "level_id" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "title" => "Sécurité informatique",
                "description" =>
                    "Les bases de la cybersécurité et des bonnes pratiques sur Internet.",
                "slug" => "securite-informatique",
                "thumbnail" => "https://images.unsplash.com/photo-1510511459019-5dda7724fd87",
                "duration" => 40,
                "max_attempts" => 2,
                "pass_score" => 75,
                "is_public" => 1,
                "user_id" => 6,
                "category_id" => 1,
                "level_id" => 2,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ];

        foreach ($quizzes as $quiz) {
            Quiz::create($quiz);
        }
    }
}
