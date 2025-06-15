<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function index()
    {
        return Score::with('user', 'quiz')->get();
    }

    public function show($id)
    {
        return Score::with('user', 'quiz')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'quiz_id' => 'required|exists:quizzes,id',
            'score' => 'required|integer',
        ]);
        try {
            $score = Score::create($data);
            return response()->json([
                'message' => 'Score créé avec succès.',
                'score' => $score
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du score.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $score = Score::findOrFail($id);
        $data = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'quiz_id' => 'sometimes|required|exists:quizzes,id',
            'score' => 'sometimes|required|integer',
        ]);
        try {
            $score->update($data);
            return response()->json([
                'message' => 'Score mis à jour avec succès.',
                'score' => $score
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du score.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = Score::destroy($id);
            if ($deleted) {
                return response()->json(['message' => 'Score supprimé avec succès.']);
            } else {
                return response()->json(['message' => 'Aucun score trouvé à supprimer.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du score.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
