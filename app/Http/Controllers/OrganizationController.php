<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        return Organization::with('teams', 'users')->get();
    }

    public function show($id)
    {
        return Organization::with('teams', 'users')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        try {
            $organization = Organization::create($data);
            return response()->json([
                'message' => 'Organisation créée avec succès.',
                'organization' => $organization
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la création de l'organisation.",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $org = Organization::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);
        try {
            $org->update($data);
            return response()->json([
                'message' => 'Organisation mise à jour avec succès.',
                'organization' => $org
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la mise à jour de l'organisation.",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = Organization::destroy($id);
            if ($deleted) {
                return response()->json(['message' => 'Organisation supprimée avec succès.']);
            } else {
                return response()->json(['message' => 'Aucune organisation trouvée à supprimer.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la suppression de l'organisation.",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
