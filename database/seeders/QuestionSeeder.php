<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        Question::create([
            "quiz_id" => 1,
            "content" => "Quelle commande permet de créer un contrôleur dans Laravel ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 1,
            "content" => "Quel fichier contient la configuration de la base de données ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 1,
            "content" => "Quel pattern architectural Laravel utilise-t-il ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 1,
            "content" => "Quelle méthode HTTP est utilisée pour créer une ressource dans une API REST ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 1,
            "content" => "Quel ORM Laravel utilise-t-il par défaut ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 2,
            "content" => "Quelle est la différence entre '==' et '===' en PHP ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 2,
            "content" => "Quel système de type PHP utilise-t-il depuis PHP 7 ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 2,
            "content" => "Quel mot-clé permet de déclarer une interface en PHP ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 2,
            "content" => "Comment déclare-t-on une constante de classe en PHP ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 2,
            "content" => "Ordonnez les étapes du cycle de vie d'une requête PHP",
            "question_type_id" => 4,
        ]);

        Question::create([
            "quiz_id" => 3,
            "content" => "Quelle syntaxe permet de déclarer une fonction fléchée en ES6 ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 3,
            "content" => "Quel mot-clé introduit le block scoping en JavaScript ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 3,
            "content" => "Quelle méthode permet de déstructurer un objet ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 3,
            "content" => "Comment importe-t-on un module par défaut en ES6 ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 3,
            "content" => "Ordonnez les états d'une Promise",
            "question_type_id" => 4,
        ]);

        Question::create([
            "quiz_id" => 4,
            "content" => "Quelle clause SQL permet de filtrer les résultats ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 4,
            "content" => "Quel type de clé garantit l'unicité dans une table ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 4,
            "content" => "Quelle commande SQL permet de créer une table ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 4,
            "content" => "Qu'est-ce qu'une jointure INNER JOIN ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 4,
            "content" => "Ordonnez les étapes d'optimisation d'une requête SQL",
            "question_type_id" => 4,
        ]);

        Question::create([
            "quiz_id" => 5,
            "content" => "Qu'est-ce qu'un composant React ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 5,
            "content" => "Quelle caractéristique ont les props en React ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 5,
            "content" => "Quel hook permet de gérer l'état local d'un composant ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 5,
            "content" => "Comment crée-t-on un composant fonctionnel en React ?",
            "question_type_id" => 3,
        ]);

        Question::create([
            "quiz_id" => 5,
            "content" => "Ordonnez le cycle de vie d'un composant React",
            "question_type_id" => 4,
        ]);
    }
}
