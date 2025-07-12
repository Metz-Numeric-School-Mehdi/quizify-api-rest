<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function index()
    {
        return Answer::with('question')->get();
    }

    public function show($id)
    {
        return Answer::with('question')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answers' => 'required|array|min:1',
            'answers.*.content' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ], [
            'question_id.required' => 'La question est obligatoire.',
            'question_id.exists' => 'La question sélectionnée est invalide.',
            'answers.required' => 'Les réponses sont obligatoires.',
            'answers.array' => 'Les réponses doivent être un tableau.',
            'answers.*.content.required' => 'Le contenu de chaque réponse est obligatoire.',
            'answers.*.is_correct.required' => 'Chaque réponse doit indiquer si elle est correcte ou non.',
        ]);

        try {
            $createdAnswers = [];
            foreach ($data['answers'] as $answerData) {
                $createdAnswers[] = Answer::create([
                    'question_id' => $data['question_id'],
                    'content' => $answerData['content'],
                    'is_correct' => $answerData['is_correct'],
                ]);
            }
            return response()->json([
                'message' => 'Réponses créées avec succès.',
                'answers' => $createdAnswers,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création des réponses.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id = null)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answers' => 'required|array|min:1',
            'answers.*.id' => 'required|exists:answers,id',
            'answers.*.content' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ], [
            'question_id.required' => 'La question est obligatoire.',
            'question_id.exists' => 'La question sélectionnée est invalide.',
            'answers.required' => 'Les réponses sont obligatoires.',
            'answers.array' => 'Les réponses doivent être un tableau.',
            'answers.*.id.required' => 'L\'ID de chaque réponse est obligatoire.',
            'answers.*.id.exists' => 'La réponse sélectionnée est invalide.',
            'answers.*.content.required' => 'Le contenu de chaque réponse est obligatoire.',
            'answers.*.is_correct.required' => 'Chaque réponse doit indiquer si elle est correcte ou non.',
        ]);

        try {
            $updatedAnswers = [];
            foreach ($data['answers'] as $answerData) {
                $answer = Answer::where('question_id', $data['question_id'])->findOrFail($answerData['id']);
                $answer->update([
                    'content' => $answerData['content'],
                    'is_correct' => $answerData['is_correct'],
                ]);
                $updatedAnswers[] = $answer;
            }
            return response()->json([
                'message' => 'Réponses mises à jour avec succès.',
                'answers' => $updatedAnswers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour des réponses.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = Answer::destroy($id);
            if ($deleted) {
                return response()->json(['message' => 'Réponse supprimée avec succès.']);
            } else {
                return response()->json(['message' => 'Aucune réponse trouvée à supprimer.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la réponse.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
