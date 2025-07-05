<?php

namespace App\Http\Controllers;

use App\Models\QuestionType;
use Illuminate\Http\Request;

class QuestionTypeController extends Controller
{
    public function index()
    {
        return QuestionType::all();
    }

    public function show($id)
    {
        return QuestionType::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        try {
            $questionType = QuestionType::create($data);
            return response()->json([
                'message' => 'Type de question créé avec succès.',
                'question_type' => $questionType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du type de question.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $type = QuestionType::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);
        try {
            $type->update($data);
            return response()->json([
                'message' => 'Type de question mis à jour avec succès.',
                'question_type' => $type
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du type de question.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = QuestionType::destroy($id);
            if ($deleted) {
                return response()->json(['message' => 'Type de question supprimé avec succès.']);
            } else {
                return response()->json(['message' => 'Aucun type de question trouvé à supprimer.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du type de question.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
