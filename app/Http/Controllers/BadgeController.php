<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()
    {
        return Badge::all();
    }

    public function show($id)
    {
        return Badge::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);
        try {
            $badge = Badge::create($data);
            return response()->json([
                'message' => 'Badge créé avec succès.',
                'badge' => $badge
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du badge.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $badge = Badge::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);
        try {
            $badge->update($data);
            return response()->json([
                'message' => 'Badge mis à jour avec succès.',
                'badge' => $badge
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du badge.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = Badge::destroy($id);
            if ($deleted) {
                return response()->json(['message' => 'Badge supprimé avec succès.']);
            } else {
                return response()->json(['message' => 'Aucun badge trouvé à supprimer.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du badge.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
