<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $quizzes = Quiz::with(["tags", "level", "category", "user"])->get();

        if ($quizzes->isEmpty()) {
            return response()->json(
                [
                    "message" => "Aucun quiz trouvé.",
                ],
                404
            );
        }

        // On ne charge pas les questions/réponses dans l'index pour chaque quiz
        return response()->json(QuizResource::collection($quizzes));
    }

    /**
     * Soumettre les réponses d'un utilisateur à un quiz et vérifier leur exactitude.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $quizId
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request, $quizId)
    {
        try {
            $validated = $request->validate([
                "responses" => "required|array",
                "responses.*.question_id" => "required|integer|exists:questions,id",
                "responses.*.answer_id" => "nullable|integer|exists:answers,id",
                "responses.*.user_answer" => "nullable|string",
            ]);

            $quiz = \App\Models\Quiz::with("questions.answers")->findOrFail($quizId);
            $user = $request->user();
            if (!$user) {
                return response()->json(["error" => "Utilisateur non authentifié"], 401);
            }
            $userId = $user->id;
            $score = 0;
            $results = [];

            foreach ($validated["responses"] as $response) {
                $question = $quiz->questions->where("id", $response["question_id"])->first();
                if (!$question) {
                    return response()->json(
                        ["error" => "Question non trouvée: " . $response["question_id"]],
                        404
                    );
                }
                $correctAnswer = $question->answers->where("is_correct", true)->first();
                $isCorrect = false;

                if (isset($response["answer_id"])) {
                    $isCorrect = $correctAnswer && $correctAnswer->id == $response["answer_id"];
                } elseif (isset($response["user_answer"])) {
                    $isCorrect =
                        strtolower(trim($correctAnswer->content ?? "")) ===
                        strtolower(trim($response["user_answer"]));
                }

                \App\Models\QuestionResponse::create([
                    "quiz_id" => $quiz->id,
                    "user_id" => $userId,
                    "question_id" => $question->id,
                    "answer_id" => $response["answer_id"] ?? null,
                    "user_answer" => $response["user_answer"] ?? null,
                    "is_correct" => $isCorrect,
                ]);

                $results[] = [
                    "question_id" => $question->id,
                    "is_correct" => $isCorrect,
                ];
                if ($isCorrect) {
                    $score++;
                }
            }

            return response()->json([
                "score" => $score,
                "total" => count($quiz->questions),
                "results" => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString(),
                ],
                500
            );
        }
    }

    /**
     * Store a newly created quiz in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "level_id" => "required|integer|exists:quiz_levels,id",
            "category_id" => "required|integer|exists:categories,id",
            "is_public" => "boolean",
            "status" => "required|in:draft,published,archived",
            "duration" => "nullable|integer",
            "max_attempts" => "nullable|integer",
            "pass_score" => "nullable|integer",
            "thumbnail" => "nullable|image|max:3000",
        ], [
            "title.required" => "Le titre est obligatoire.",
            "title.string" => "Le titre doit être une chaîne de caractères.",
            "title.max" => "Le titre ne doit pas dépasser 255 caractères.",
            "level_id.required" => "Le niveau est obligatoire.",
            "level_id.integer" => "Le niveau doit être un entier.",
            "level_id.exists" => "Le niveau sélectionné est invalide.",
            "category_id.required" => "La catégorie est obligatoire.",
            "category_id.integer" => "La catégorie doit être un entier.",
            "category_id.exists" => "La catégorie sélectionnée est invalide.",
            "description.string" => "La description doit être une chaîne de caractères.",
            "is_public.boolean" => "Le champ public doit être vrai ou faux.",
            "status.required" => "Le statut est obligatoire.",
            "status.in" =>
                "Le statut doit être l'une des valeurs suivantes : draft, published, archived.",
            "duration.integer" => "La durée doit être un entier.",
            "max_attempts.integer" => "Le nombre maximum de tentatives doit être un entier.",
            "pass_score.integer" => "Le score de passage doit être un entier.",
            "thumbnail.max" => "La miniature ne doit pas dépasser 3000 Ko",
        ]);

        try {
            if ($request->hasFile("thumbnail")) {
                $file = $request->file("thumbnail");
                $filename = "quiz_" . uniqid() . "." . $file->getClientOriginalExtension();
                Storage::disk("minio")->putFileAs('', $file, $filename);
                $validatedData["thumbnail"] = $filename;
            }

            $validatedData["slug"] = $this->generateUniqueSlug($validatedData["title"]);

            $quiz = $request->user()->quizzesCreated()->create($validatedData);
            $quiz->load(["level", "user", "tags", "category"]);
            return response()->json(new QuizResource($quiz), 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du quiz.',
                'error' => $e->getMessage(),
            ], 500);
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
        $quiz = Quiz::with([
            "tags",
            "level",
            "category",
            "user",
            "questions.answers"
        ])->find($id);

        if (!$quiz) {
            return response()->json([
                "message" => "Quiz non trouvé",
            ], 404);
        }

        return response()->json(new QuizResource($quiz));
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
            return response()->json([
                "message" => "Quiz non trouvé.",
            ], 404);
        }

        if ($quiz->user_id !== $request->user()->id) {
            return response()->json([
                "message" => "Vous n'êtes pas autorisé à modifier ce quiz.",
            ], 403);
        }

        try {
            $validatedData = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "level_id" => "required|integer|exists:quiz_levels,id",
                "category_id" => "required|integer|exists:categories,id",
                "is_public" => "boolean",
                "status" => "required|in:draft,published,archived",
                "duration" => "nullable|integer",
                "max_attempts" => "nullable|integer",
                "pass_score" => "nullable|integer",
                "thumbnail" => "nullable|string|max:255",
            ], [
                "title.required" => "Le titre est obligatoire.",
                "title.max" => "Le titre ne doit pas dépasser 255 caractères.",
                "level_id.required" => "Le niveau est obligatoire.",
                "category_id.required" => "La catégorie est obligatoire.",
                "description.string" => "La description doit être une chaîne de caractères.",
                "is_public.boolean" => "Le champ public doit être vrai ou faux.",
                "status.required" => "Le statut est obligatoire.",
            ]);

            if ($request->has("title")) {
                $validatedData["slug"] = Str::slug($request->input("title"));
            }

            $quiz->update($validatedData);
            $quiz->load(["level", "user", "tags", "category", "questions.answers"]);

            if ($request->has("tags")) {
                $quiz->tags()->sync($request->tags);
            }

            return response()->json(new QuizResource($quiz), 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du quiz',
                'error' => $e->getMessage(),
            ], 500);
        }
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

        try {
            $quiz->tags()->detach();
            $quiz->delete();

            return response()->json(
                [
                    "message" => "Quiz supprimé avec succès.",
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du quiz.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
