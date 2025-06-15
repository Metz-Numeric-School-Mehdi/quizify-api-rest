<?php

namespace App\Http\Controllers;

use App\Models\QuestionResponse;
use Illuminate\Http\Request;

class QuestionResponseController extends Controller
{
    public function index()
    {
        return QuestionResponse::with('user', 'quiz', 'question', 'answer')->get();
    }

    public function show($id)
    {
        return QuestionResponse::with('user', 'quiz', 'question', 'answer')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'user_id' => 'required|exists:users,id',
            'question_id' => 'required|exists:questions,id',
            'answer_id' => 'nullable|exists:answers,id',
            'user_answer' => 'nullable|string',
            'user_response_data' => 'nullable|json',
            'is_correct' => 'boolean',
            'points' => 'integer',
            'response_time' => 'integer',
        ]);
        try {
            $questionResponse = QuestionResponse::create($data);
            return response()->json([
                'message' => 'Réponse à la question créée avec succès.',
                'question_response' => $questionResponse
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la réponse à la question.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $qr = QuestionResponse::findOrFail($id);
        $data = $request->validate([
            'quiz_id' => 'sometimes|required|exists:quizzes,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'question_id' => 'sometimes|required|exists:questions,id',
            'answer_id' => 'nullable|exists:answers,id',
            'user_answer' => 'nullable|string',
            'user_response_data' => 'nullable|json',
            'is_correct' => 'boolean',
            'points' => 'integer',
            'response_time' => 'integer',
        ]);
        try {
            $qr->update($data);
            return response()->json([
                'message' => 'Réponse à la question mise à jour avec succès.',
                'question_response' => $qr
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la réponse à la question.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = QuestionResponse::destroy($id);
            if ($deleted) {
                return response()->json(['message' => 'Réponse à la question supprimée avec succès.']);
            } else {
                return response()->json(['message' => 'Aucune réponse à la question trouvée à supprimer.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la réponse à la question.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
