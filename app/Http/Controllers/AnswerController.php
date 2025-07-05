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
            'content' => 'required|string',
            'is_correct' => 'required|boolean',
        ], [
            'question_id.required' => 'La question est obligatoire.',
            'question_id.exists' => 'La question sélectionnée est invalide.',
            'content.required' => 'Le contenu est obligatoire.',
            'content.string' => 'Le contenu doit être une chaîne de caractères.',
            'is_correct.required' => 'La réponse doit indiquer si elle est correcte ou non.',
        ]);

        try {
            $answer = Answer::create($data);
            return response()->json($answer, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la réponse.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $answer = Answer::findOrFail($id);
        $validatedData = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'content' => 'required|string',
            'is_correct' => 'required|boolean',
        ], [
            'question_id.required' => 'La question est obligatoire.',
            'question_id.exists' => 'La question sélectionnée est invalide.',
            'content.required' => 'Le contenu est obligatoire.',
            'content.string' => 'Le contenu doit être une chaîne de caractères.',
            'is_correct.required' => 'La réponse doit indiquer si elle est correcte ou non.',
        ]);

        try {
            $answer->update($validatedData);
            return response()->json([
                'message' => 'Réponse mise à jour avec succès.',
                'answer' => $answer,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la réponse.',
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
