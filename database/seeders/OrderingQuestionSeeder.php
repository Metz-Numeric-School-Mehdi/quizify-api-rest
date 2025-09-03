<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Answer;
use Illuminate\Database\Seeder;

class OrderingQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderingType = QuestionType::where('name', 'Remise dans l\'ordre')->first();

        if (!$orderingType) {
            $orderingType = QuestionType::create(['name' => 'Remise dans l\'ordre']);
        }

        $quiz = Quiz::first();

        if (!$quiz) {
            return;
        }

        $question1 = Question::create([
            'quiz_id' => $quiz->id,
            'content' => 'Remettez dans l\'ordre chronologique les étapes de création d\'une application web',
            'question_type_id' => $orderingType->id
        ]);

        $steps = [
            ['content' => 'Analyser les besoins du client', 'order_position' => 1],
            ['content' => 'Créer les maquettes et wireframes', 'order_position' => 2],
            ['content' => 'Développer le backend et l\'API', 'order_position' => 3],
            ['content' => 'Développer le frontend', 'order_position' => 4],
            ['content' => 'Tester l\'application', 'order_position' => 5],
            ['content' => 'Déployer en production', 'order_position' => 6]
        ];

        foreach ($steps as $step) {
            Answer::create([
                'question_id' => $question1->id,
                'content' => $step['content'],
                'is_correct' => true,
                'order_position' => $step['order_position']
            ]);
        }

        $question2 = Question::create([
            'quiz_id' => $quiz->id,
            'content' => 'Ordonnez les couches du modèle OSI du niveau le plus bas au plus haut',
            'question_type_id' => $orderingType->id
        ]);

        $osiLayers = [
            ['content' => 'Couche physique', 'order_position' => 1],
            ['content' => 'Couche liaison de données', 'order_position' => 2],
            ['content' => 'Couche réseau', 'order_position' => 3],
            ['content' => 'Couche transport', 'order_position' => 4],
            ['content' => 'Couche session', 'order_position' => 5],
            ['content' => 'Couche présentation', 'order_position' => 6],
            ['content' => 'Couche application', 'order_position' => 7]
        ];

        foreach ($osiLayers as $layer) {
            Answer::create([
                'question_id' => $question2->id,
                'content' => $layer['content'],
                'is_correct' => true,
                'order_position' => $layer['order_position']
            ]);
        }
    }
}
