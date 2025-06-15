<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
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
            'is_correct' => 'required|boolean',
        ], [
            'question_id.required' => 'La question est obligatoire.',
            'question_id.exists' => 'La question sélectionnée est invalide.',
            'content.required' => 'Le contenu est obligatoire.',
            'content.string' => 'Le contenu doit être une chaîne de caractères.',
            'is_correct.required' => 'La réponse doit indiquer si elle est correcte ou non.',
        ]);
        $answer = Answer::create($data);
        return response()->json($answer, 201);
    }

    public function update(Request $request, $id)
    {
        $answer = Answer::findOrFail($id);
        $validatedData = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'content' => 'required|string',
            'is_correct' => 'required|boolean',
        ], [
            'question_id.required' => 'La question est obligatoire.',
            'question_id.exists' => 'La question sélectionnée est invalide.',
            'content.required' => 'Le contenu est obligatoire.',
            'content.string' => 'Le contenu doit être une chaîne de caractères.',
            'is_correct.required' => 'La réponse doit indiquer si elle est correcte ou non.',
        ]);
        $answer->update($validatedData);
        return response()->json([
            'message' => 'Réponse mise à jour avec succès.',
            'answer' => $answer,
        ], 200);
    }

    public function destroy($id)
    {
        Answer::destroy($id);
        return response()->json(['message' => 'Answer deleted']);
    }
}
