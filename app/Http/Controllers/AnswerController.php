<?php

namespace App\Http\Controllers;

use App\Models\Answer;
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
            'is_correct' => 'boolean',
        ]);
        return Answer::create($data);
    }

    public function update(Request $request, $id)
    {
        $answer = Answer::findOrFail($id);
        $answer->update($request->all());
        return $answer;
    }

    public function destroy($id)
    {
        Answer::destroy($id);
        return response()->json(['message' => 'Answer deleted']);
    }
}
