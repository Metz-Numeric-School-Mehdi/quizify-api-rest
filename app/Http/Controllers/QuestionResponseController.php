<?php

namespace App\Http\Controllers;

use App\Models\QuestionResponse;
use Illuminate\Http\Request;

class QuestionResponseController extends Controller
{
    public function index()
    {
        return QuestionResponse::with('user', 'quiz', 'question', 'answer')->get();
    }

    public function show($id)
    {
        return QuestionResponse::with('user', 'quiz', 'question', 'answer')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'user_id' => 'required|exists:users,id',
            'question_id' => 'required|exists:questions,id',
            'answer_id' => 'nullable|exists:answers,id',
            'user_answer' => 'nullable|string',
            'user_response_data' => 'nullable|json',
            'is_correct' => 'boolean',
            'points' => 'integer',
            'response_time' => 'integer',
        ]);
        return QuestionResponse::create($data);
    }

    public function update(Request $request, $id)
    {
        $qr = QuestionResponse::findOrFail($id);
        $qr->update($request->all());
        return $qr;
    }

    public function destroy($id)
    {
        QuestionResponse::destroy($id);
        return response()->json(['message' => 'Question response deleted']);
    }
}
