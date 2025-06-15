<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Exception;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of questions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $questions = Question::with('answers')->get();
        if ($questions->isEmpty()) {
            return response()->json([
                'message' => 'Aucune question trouvée',
            ], 404);
        }
        return QuestionResource::collection($questions);
    }

    /**
     * Display the specified question.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $question = Question::with('answers')->find($id);
        if (!$question) {
            return response()->json([
                'message' => 'Question non trouvée',
            ], 404);
        }
        return new QuestionResource($question);
    }

    /**
     * Store a newly created question in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                'quiz_id' => 'required|integer|exists:quizzes,id',
                'content' => 'required|string|max:255',
                'question_type_id' => 'required|integer|exists:question_types,id',
            ],
            [
                'quiz_id.required' => 'Le quiz est obligatoire.',
                'quiz_id.integer' => 'Le quiz doit être un entier.',
                'quiz_id.exists' => 'Le quiz sélectionné est invalide.',
                'content.required' => 'Le contenu est obligatoire.',
                'content.string' => 'Le contenu doit être une chaîne de caractères.',
                'content.max' => 'Le contenu ne doit pas dépasser 255 caractères.',
                'question_type_id.required' => 'Le type de question est obligatoire.',
                'question_type_id.integer' => 'Le type de question doit être un entier.',
                'question_type_id.exists' => 'Le type de question sélectionné est invalide.',
            ]
        );
        try {
            $question = Question::create($validatedData);
            $question->load('answers');
            return new QuestionResource($question);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la question.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Destroy the specified question.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $question = Question::find($id);
        if (!$question) {
            return response()->json([
                "message" => "Question non trouvée.",
            ], 404);
        }
        if (!$question->quiz) {
            return response()->json([
                "message" => "Quiz associé introuvable pour cette question.",
            ], 404);
        }
        if (!$question->quiz->user_id) {
            return response()->json([
                "message" => "Le quiz n'a pas de créateur (user_id manquant).",
            ], 403);
        }
        if ($question->quiz->user_id !== $request->user()->id) {
            return response()->json([
                "message" => "Vous n'êtes pas autorisé à supprimer cette question.",
            ], 403);
        }
        $question->delete();
        return response()->json([
            "message" => "Question supprimée avec succès.",
        ], 200);
    }

    /**
     * Update the specified question.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $question = Question::find($id);
        if (!$question) {
            return response()->json([
                "message" => "Question non trouvée.",
            ], 404);
        }
        if (!$question->quiz) {
            return response()->json([
                "message" => "Quiz associé introuvable pour cette question.",
            ], 404);
        }
        if (!$question->quiz->user_id) {
            return response()->json([
                "message" => "Le quiz n'a pas de créateur (user_id manquant).",
            ], 403);
        }
        if ($question->quiz->user_id !== $request->user()->id) {
            return response()->json([
                "message" => "Vous n'êtes pas autorisé à modifier cette question.",
            ], 403);
        }
        $validatedData = $request->validate([
            "quiz_id" => "required|integer|exists:quizzes,id",
            "content" => "required|string|max:255",
            "question_type_id" => "required|integer|exists:question_types,id",
        ], [
            "quiz_id.required" => "Le quiz est obligatoire.",
            "quiz_id.integer" => "Le quiz doit être un entier.",
            "quiz_id.exists" => "Le quiz sélectionné est invalide.",
            "content.required" => "Le contenu est obligatoire.",
            "content.string" => "Le contenu doit être une chaîne de caractères.",
            "content.max" => "Le contenu ne doit pas dépasser 255 caractères.",
            "question_type_id.required" => "Le type de question est obligatoire.",
            "question_type_id.integer" => "Le type de question doit être un entier.",
            "question_type_id.exists" => "Le type de question sélectionné est invalide.",
        ]);
        $question->update($validatedData);
        return response()->json([
            "message" => "Question mise à jour avec succès.",
            "question" => $question,
        ], 200);
    }
}
