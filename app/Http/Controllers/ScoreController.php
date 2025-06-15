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
        return Score::create($data);
    }

    public function update(Request $request, $id)
    {
        $score = Score::findOrFail($id);
        $score->update($request->all());
        return $score;
    }

    public function destroy($id)
    {
        Score::destroy($id);
        return response()->json(['message' => 'Score deleted']);
    }
}
