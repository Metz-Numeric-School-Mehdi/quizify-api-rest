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
        return Team::create($data);
    }

    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        $team->update($request->all());
        return $team;
    }

    public function destroy($id)
    {
        Team::destroy($id);
        return response()->json(['message' => 'Team deleted']);
    }
}
