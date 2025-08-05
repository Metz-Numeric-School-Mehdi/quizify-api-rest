<?php

namespace App\Http\Controllers;

use App\Repositories\Quiz\QuizRepository;
use App\Http\Controllers\CRUDController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends CRUDController
{
    /**
     * The repository instance.
     *
     * @var QuizRepository
     */
    protected $repository;

    /**
     * Create a new controller instance.
     *
     * @param QuizRepository $repository
     */
    public function __construct(QuizRepository $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    /**
     * Handles the submission of quiz responses by a user or guest.
     *
     * Validates the incoming request to ensure that the responses array is present and properly structured.
     * Each response must include a valid question ID, and may include an answer ID or a user-provided answer.
     * Submits the responses to the repository for processing and returns the result as a JSON response.
     * If an exception occurs during processing, returns a JSON error response with a 500 status code.
     *
     * @param Request $request
     * @param int $quizId
     * @return JsonResponse
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

            $user = $request->user();

            $result = $this->repository->submit($user, $quizId, $validated["responses"]);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Search quizzes using ElasticSearch via Laravel Scout.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'q' => 'nullable|string',
                'level_id' => 'nullable|integer|exists:quiz_levels,id',
                'category_id' => 'nullable|integer|exists:categories,id',
                'status' => 'nullable|in:draft,published,archived',
                'is_public' => 'nullable|boolean',
            ]);

            $perPage = 10;
            $searchQuery = $validated['q'] ?? '';

            $builder = \App\Models\Quiz::search($searchQuery);

            if (!empty($validated['level_id'])) {
                $builder = $builder->where('level_id', $validated['level_id']);
            }
            if (!empty($validated['category_id'])) {
                $builder = $builder->where('category_id', $validated['category_id']);
            }
            if (!empty($validated['status'])) {
                $builder = $builder->where('status', $validated['status']);
            }
            if (isset($validated['is_public'])) {
                $builder = $builder->where('is_public', (bool)$validated['is_public']);
            }

            try {
                $quizzes = $builder->paginate($perPage);
            } catch (\Exception $e) {
                $query = \App\Models\Quiz::query();

                if (!empty($searchQuery)) {
                    $query->where(function($q) use ($searchQuery) {
                        $q->where('title', 'LIKE', "%{$searchQuery}%")
                          ->orWhere('description', 'LIKE', "%{$searchQuery}%");
                    });
                }

                if (!empty($validated['level_id'])) {
                    $query->where('level_id', $validated['level_id']);
                }

                if (!empty($validated['category_id'])) {
                    $query->where('category_id', $validated['category_id']);
                }

                if (!empty($validated['status'])) {
                    $query->where('status', $validated['status']);
                }

                if (isset($validated['is_public'])) {
                    $query->where('is_public', (bool)$validated['is_public']);
                }

                $quizzes = $query->paginate($perPage);
                \Illuminate\Support\Facades\Log::error('ElasticSearch error: ' . $e->getMessage());
            }

            if ($quizzes->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun quiz trouvé pour cette recherche.'
                ], 404);
            }

            return response()->json([
                'data' => \App\Http\Resources\QuizResource::collection($quizzes->items()),
                'pagination' => [
                    'total' => $quizzes->total(),
                    'per_page' => $quizzes->perPage(),
                    'current_page' => $quizzes->currentPage(),
                    'last_page' => $quizzes->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche: ' . $e->getMessage()
            ], 500);
        }
    }
}
