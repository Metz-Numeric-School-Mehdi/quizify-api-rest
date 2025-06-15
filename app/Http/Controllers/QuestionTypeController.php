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
        return QuestionType::create($data);
    }

    public function update(Request $request, $id)
    {
        $type = QuestionType::findOrFail($id);
        $type->update($request->all());
        return $type;
    }

    public function destroy($id)
    {
        QuestionType::destroy($id);
        return response()->json(['message' => 'Question type deleted']);
    }
}
