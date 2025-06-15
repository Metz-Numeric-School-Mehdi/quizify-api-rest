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
        return Badge::create($data);
    }

    public function update(Request $request, $id)
    {
        $badge = Badge::findOrFail($id);
        $badge->update($request->all());
        return $badge;
    }

    public function destroy($id)
    {
        Badge::destroy($id);
        return response()->json(['message' => 'Badge deleted']);
    }
}
