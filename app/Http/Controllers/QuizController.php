<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Display a listing of the quizzes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $quizzes = Quiz::with("tags", "level", 'category')->get();

        if ($quizzes->isEmpty()) {
            return response()->json(
                [
                    "message" => "Aucun quiz trouvé.",
                ],
                404
            );
        }
        return QuizResource::collection($quizzes);
    }

    /**
     * Store a newly created quiz in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "level_id" => "required|integer|exists:quiz_levels,id",
                "is_public" => "boolean",
                "status" => "required|in:draft,published,archived",
                "duration" => "nullable|integer",
                "max_attempts" => "nullable|integer",
                "pass_score" => "nullable|integer",
                "thumbnail" => "nullable|string|max:255",
            ],
            [
                "title.required" => "Le titre est obligatoire.",
                "title.string" => "Le titre doit être une chaîne de caractères.",
                "title.max" => "Le titre ne doit pas dépasser 255 caractères.",
                "level_id.required" => "Le niveau est obligatoire.",
                "level_id.integer" => "Le niveau doit être un entier.",
                "level_id.exists" => "Le niveau sélectionné est invalide.",
                "description.string" => "La description doit être une chaîne de caractères.",
                "is_public.boolean" => "Le champ public doit être vrai ou faux.",
                "status.required" => "Le statut est obligatoire.",
                "status.in" =>
                    "Le statut doit être l'une des valeurs suivantes : draft, published, archived.",
                "duration.integer" => "La durée doit être un entier.",
                "max_attempts.integer" => "Le nombre maximum de tentatives doit être un entier.",
                "pass_score.integer" => "Le score de passage doit être un entier.",
                "thumbnail.string" => "La miniature doit être une chaîne de caractères.",
                "thumbnail.max" => "La miniature ne doit pas dépasser 255 caractères.",
            ]
        );

        $validatedData["slug"] = $this->generateUniqueSlug($validatedData["title"]);

        try {
            $quiz = $request->user()->quizzes()->create($validatedData);
            if ($request->has("tags")) {
                $quiz->tags()->sync($request->tags);
            }
            $quiz->load("tags", "level");
            return response()->json($quiz, 201);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified quiz.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $quiz = Quiz::with("tags", "level", "category")->find($id);

        if (!$quiz) {
            return response()->json(
                [
                    "message" => "Question non trouvée",
                ],
                404
            );
        }

        return new QuizResource($quiz);
    }

    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $i = 1;

        while (Quiz::where("slug", $slug)->exists()) {
            $slug = $originalSlug . "-" . $i++;
        }

        return $slug;
    }

    /**
     * Update the specified quiz.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::find($id);

        if (!$quiz) {
            return response()->json(
                [
                    "message" => "Quiz non trouvé.",
                ],
                404
            );
        }

        if ($quiz->user_id !== $request->user()->id) {
            return response()->json(
                [
                    "message" => "Vous n'êtes pas autorisé à modifier ce quiz.",
                ],
                403
            );
        }

        $validatedData = $request->validate(
            [
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "level_id" => "required|integer|exists:quiz_levels,id",
                "is_public" => "boolean",
                "status" => "required|in:draft,published,archived",
                "duration" => "nullable|integer",
                "max_attempts" => "nullable|integer",
                "pass_score" => "nullable|integer",
                "thumbnail" => "nullable|string|max:255",
            ],
            [
                "title.required" => "Le titre est obligatoire.",
                "title.string" => "Le titre doit être une chaîne de caractères.",
                "title.max" => "Le titre ne doit pas dépasser 255 caractères.",
                "level_id.required" => "Le niveau est obligatoire.",
                "level_id.integer" => "Le niveau doit être un entier.",
                "level_id.exists" => "Le niveau sélectionné est invalide.",
                "description.string" => "La description doit être une chaîne de caractères.",
                "is_public.boolean" => "Le champ public doit être vrai ou faux.",
                "status.required" => "Le statut est obligatoire.",
                "status.in" =>
                    "Le statut doit être l'une des valeurs suivantes : draft, published, archived.",
                "duration.integer" => "La durée doit être un entier.",
                "max_attempts.integer" => "Le nombre maximum de tentatives doit être un entier.",
                "pass_score.integer" => "Le score de passage doit être un entier.",
                "thumbnail.string" => "La miniature doit être une chaîne de caractères.",
                "thumbnail.max" => "La miniature ne doit pas dépasser 255 caractères.",
            ]
        );

        if ($request->has("title")) {
            $validatedData["slug"] = Str::slug($request->input("title"));
        }

        $quiz->update($validatedData);

        if ($request->has("tags")) {
            $quiz->tags()->sync($request->tags);
        }

        return response()->json(
            [
                "message" => "Quiz mis à jour avec succès.",
                "quiz" => $quiz,
            ],
            200
        );
    }

    /**
     * Destroy the specified quiz.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json(
                [
                    "message" => "Quiz non trouvé.",
                ],
                404
            );
        }

        if ($quiz->user_id !== $request->user()->id) {
            return response()->json(
                [
                    "message" => "Vous n'êtes pas autorisé à supprimer ce quiz.",
                ],
                403
            );
        }

        $quiz->tags()->detach();
        $quiz->delete();

        return response()->json(
            [
                "message" => "Quiz supprimé avec succès.",
            ],
            200
        );
    }
}
