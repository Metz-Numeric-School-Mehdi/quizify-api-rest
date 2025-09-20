<?php

namespace App\Services;

use App\Repositories\Quiz\QuizRepository;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class QuizService
{
    protected QuizRepository $quizRepository;

    public function __construct(QuizRepository $quizRepository)
    {
        $this->quizRepository = $quizRepository;
    }

    /**
     * Handle the submission of quiz responses by a user or guest.
     *
     * Validates the incoming request to ensure that the responses array is present and properly structured.
     * Each response must include a valid question ID, and may include an answer ID or a user-provided answer.
     * Submits the responses to the repository for processing and returns the result.
     *
     * @param Request $request
     * @param int $quizId
     * @return array
     * @throws ValidationException
     * @throws \Exception
     */
    public function submitQuiz(Request $request, int $quizId): array
    {
        $validated = $this->validateSubmissionRequest($request);
        $responses = $this->processResponsesData($validated['responses']);

        $user = $request->user();
        $timeSpent = $validated['time_spent'] ?? null;

        return $this->quizRepository->submit($user, $quizId, $responses, $timeSpent);
    }

    /**
     * Validate the quiz submission request data.
     *
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    private function validateSubmissionRequest(Request $request): array
    {
        return $request->validate([
            'responses' => 'required|array|min:1',
            'responses.*.question_id' => 'required|integer|exists:questions,id',
            'responses.*.answer_id' => 'required_without_all:responses.*.user_answer,responses.*.user_order|nullable|integer|exists:answers,id',
            'responses.*.user_answer' => 'required_without_all:responses.*.answer_id,responses.*.user_order|nullable|string|max:1000',
            'responses.*.user_order' => 'required_without_all:responses.*.answer_id,responses.*.user_answer|nullable',
            'time_spent' => 'nullable|integer|min:0',
        ]);
    }

    /**
     * Process and validate the responses data, particularly handling user_order formatting.
     *
     * @param array $responses
     * @return array
     * @throws ValidationException
     */
    private function processResponsesData(array $responses): array
    {
        foreach ($responses as $index => &$response) {
            if (isset($response['user_order'])) {
                $response['user_order'] = $this->processUserOrder($response['user_order'], $index);
                $this->validateUserOrder($response['user_order'], $index);
            }
        }

        return $responses;
    }

    /**
     * Process user_order data, converting JSON string to array if needed.
     *
     * @param mixed $userOrder
     * @param int $index
     * @return array
     * @throws ValidationException
     */
    private function processUserOrder($userOrder, int $index): array
    {
        if (is_string($userOrder)) {
            $decoded = json_decode($userOrder, true);
            if (!is_array($decoded)) {
                throw ValidationException::withMessages([
                    "responses.{$index}.user_order" => ["Le format de user_order est invalide. Doit être un array ou un JSON valide."]
                ]);
            }
            return $decoded;
        }

        return $userOrder;
    }

    /**
     * Validate user_order array structure and content.
     *
     * @param array $userOrder
     * @param int $index
     * @throws ValidationException
     */
    private function validateUserOrder(array $userOrder, int $index): void
    {
        if (empty($userOrder)) {
            throw ValidationException::withMessages([
                "responses.{$index}.user_order" => ["user_order doit être un array non vide."]
            ]);
        }

        foreach ($userOrder as $answerId) {
            if (!is_integer($answerId) || $answerId <= 0) {
                throw ValidationException::withMessages([
                    "responses.{$index}.user_order" => ["Tous les éléments de user_order doivent être des entiers positifs."]
                ]);
            }
        }
    }

    /**
     * Search quizzes using ElasticSearch via Laravel Scout with fallback to database query.
     *
     * Validates search parameters and attempts to search using ElasticSearch.
     * Falls back to database queries if ElasticSearch fails.
     *
     * @param Request $request
     * @return array
     * @throws ValidationException
     * @throws \Exception
     */
    public function searchQuizzes(Request $request): array
    {
        $validated = $this->validateSearchRequest($request);

        $perPage = 10;
        $searchQuery = $validated['q'] ?? '';

        try {
            $quizzes = $this->searchWithElasticsearch($validated, $searchQuery, $perPage);
        } catch (\Exception $e) {
            Log::error('ElasticSearch error: ' . $e->getMessage());
            $quizzes = $this->searchWithDatabase($validated, $searchQuery, $perPage);
        }

        if ($quizzes->isEmpty()) {
            throw new \Exception('Aucun quiz trouvé pour cette recherche.');
        }

        return [
            'items' => QuizResource::collection($quizzes->items()),
            'meta' => [
                'total' => $quizzes->total(),
                'per_page' => $quizzes->perPage(),
                'current_page' => $quizzes->currentPage(),
                'last_page' => $quizzes->lastPage(),
            ],
        ];
    }

    /**
     * Validate the search request parameters.
     *
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    private function validateSearchRequest(Request $request): array
    {
        return $request->validate([
            'q' => 'nullable|string',
            'level_id' => 'nullable|integer|exists:quiz_levels,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'status' => 'nullable|in:draft,published,archived',
            'is_public' => 'nullable|boolean',
        ]);
    }

    /**
     * Search quizzes using ElasticSearch.
     *
     * @param array $validated
     * @param string $searchQuery
     * @param int $perPage
     * @return mixed
     */
    private function searchWithElasticsearch(array $validated, string $searchQuery, int $perPage)
    {
        $builder = Quiz::search($searchQuery);

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
            $builder = $builder->where('is_public', (bool) $validated['is_public']);
        }

        return $builder->paginate($perPage);
    }

    /**
     * Search quizzes using database query as fallback.
     *
     * @param array $validated
     * @param string $searchQuery
     * @param int $perPage
     * @return mixed
     */
    private function searchWithDatabase(array $validated, string $searchQuery, int $perPage)
    {
        $query = Quiz::query();

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
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
            $query->where('is_public', (bool) $validated['is_public']);
        }

        return $query->paginate($perPage);
    }
}
