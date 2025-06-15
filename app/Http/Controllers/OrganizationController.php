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
        return Organization::create($data);
    }

    public function update(Request $request, $id)
    {
        $org = Organization::findOrFail($id);
        $org->update($request->all());
        return $org;
    }

    public function destroy($id)
    {
        Organization::destroy($id);
        return response()->json(['message' => 'Organization deleted']);
    }
}
