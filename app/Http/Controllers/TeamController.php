<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        return Team::with('organization', 'users')->get();
    }

    public function show($id)
    {
        return Team::with('organization', 'users')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
        ]);
        try {
            $team = Team::create($data);
            return response()->json([
                'message' => 'Équipe créée avec succès.',
                'team' => $team
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de l\'équipe.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'organization_id' => 'sometimes|required|exists:organizations,id',
        ]);
        try {
            $team->update($data);
            return response()->json([
                'message' => 'Équipe mise à jour avec succès.',
                'team' => $team
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de l\'équipe.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = Team::destroy($id);
            if ($deleted) {
                return response()->json(['message' => 'Équipe supprimée avec succès.']);
            } else {
                return response()->json(['message' => 'Aucune équipe trouvée à supprimer.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'équipe.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
