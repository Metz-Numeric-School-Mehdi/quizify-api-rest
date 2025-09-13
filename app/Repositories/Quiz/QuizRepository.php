<?php

namespace App\Repositories\Quiz;

use App\Models\Quiz;
use App\Components\Repository;
use App\Models\QuestionResponse;
use App\Services\ElasticsearchService;
use App\Services\PointsCalculationService;
use App\Services\LeaderboardService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuizRepository extends Repository
{
    protected ?ElasticsearchService $elasticsearchService;
    protected PointsCalculationService $pointsService;
    protected LeaderboardService $leaderboardService;

    protected array $withRelations = ['questions.answers'];

    public function __construct(
        ?ElasticsearchService $elasticsearchService = null,
        ?PointsCalculationService $pointsService = null,
        ?LeaderboardService $leaderboardService = null
    ) {
        parent::__construct(new Quiz());
        $this->elasticsearchService = $elasticsearchService ?? app(ElasticsearchService::class);
        $this->pointsService = $pointsService ?? app(PointsCalculationService::class);
        $this->leaderboardService = $leaderboardService ?? app(LeaderboardService::class);
    }

    /**
     * Get all quizzes with optional filtering
     */
    public function index()
    {
        return $this->model
            ->with($this->withRelations)
            ->when(request()->boolean('mine'), fn($q) => $q->where('user_id', request()->user()->id))
            ->get();
    }

    /**
     * Retrieve a specific quiz with relations
     */
    public function show($id): Model
    {
        return $this->model->with($this->withRelations)->findOrFail($id);
    }

    /**
     * Submit quiz responses and calculate results
     */
    public function submit($user, int $quizId, array $responses, ?int $timeSpent = null): array
    {
        $quiz = $this->getQuizWithRelations($quizId);
        $startTime = microtime(true);

        $results = $this->processAllResponses($quiz, $responses, $user);
        $score = $this->calculateScore($results);
        $processingTime = microtime(true) - $startTime;

        $pointsData = $this->handleUserPoints($user, $quiz, $score, count($responses), $timeSpent);

        return $this->buildSubmissionResponse(
            $quiz,
            $score,
            count($responses),
            $results,
            $pointsData,
            $processingTime,
            $timeSpent
        );
    }

    /**
     * Store quiz with safe indexing
     */
    public function store(array $data): Model
    {
        $data = $this->prepareQuizData($data);

        $quiz = Quiz::withoutSyncingToSearch(fn() => parent::store($data));

        $this->safelyIndexQuiz($quiz);
        $quiz->load('level', 'category', 'tags');

        return $quiz;
    }

    /**
     * Update quiz with safe indexing
     */
    public function update($data, $id): Model
    {
        $data = $this->prepareQuizData($data);

        $quiz = Quiz::withoutSyncingToSearch(fn() => parent::update($data, $id));

        $this->safelyIndexQuiz($quiz);

        return $quiz;
    }

    // ============================================
    // PRIVATE METHODS - Quiz Submission Logic
    // ============================================

    private function getQuizWithRelations(int $quizId): Quiz
    {
        return Quiz::with("questions.answers", "level")->findOrFail($quizId);
    }

    private function processAllResponses(Quiz $quiz, array $responses, $user): array
    {
        $results = [];

        foreach ($responses as $response) {
            $question = $this->findQuestionInQuiz($quiz, $response["question_id"]);
            $isCorrect = $this->validateResponse($question, $response);

            $this->saveQuestionResponse($quiz, $question, $response, $isCorrect, $user);

            $results[] = [
                "question_id" => $question->id,
                "is_correct" => $isCorrect,
            ];
        }

        return $results;
    }

    private function findQuestionInQuiz(Quiz $quiz, int $questionId)
    {
        $question = $quiz->questions->where("id", $questionId)->first();

        if (!$question) {
            throw new \Exception("Question non trouvée: {$questionId}");
        }

        return $question;
    }

    private function validateResponse($question, array $response): bool
    {
        if ($question->question_type_id == 4 && isset($response["user_order"])) {
            return $this->validateOrderingQuestion($question, $response["user_order"]);
        }

        if (isset($response["answer_id"])) {
            return $this->validateMultipleChoiceQuestion($question, $response["answer_id"]);
        }

        if (isset($response["user_answer"])) {
            return $this->validateOpenQuestion($question, $response["user_answer"]);
        }

        return false;
    }

    private function validateMultipleChoiceQuestion($question, int $answerId): bool
    {
        $correctAnswer = $question->answers->where("is_correct", true)->first();
        return $correctAnswer && $correctAnswer->id == $answerId;
    }

    private function validateOpenQuestion($question, string $userAnswer): bool
    {
        $correctAnswer = $question->answers->where("is_correct", true)->first();

        return strtolower(trim($correctAnswer->content ?? "")) ===
               strtolower(trim($userAnswer));
    }

    private function validateOrderingQuestion($question, array $userOrder): bool
    {
        $correctOrder = $question->answers()
            ->orderBy('order_position')
            ->pluck('id')
            ->toArray();

        return $correctOrder === array_map('intval', $userOrder);
    }

    private function saveQuestionResponse(Quiz $quiz, $question, array $response, bool $isCorrect, $user): void
    {
        QuestionResponse::create([
            "quiz_id" => $quiz->id,
            "question_id" => $question->id,
            "answer_id" => $response["answer_id"] ?? null,
            "user_answer" => $response["user_answer"] ?? null,
            "user_response_data" => isset($response["user_order"])
                ? json_encode($response["user_order"])
                : null,
            "is_correct" => $isCorrect,
            "user_id" => $user?->id,
        ]);
    }

    private function calculateScore(array $results): int
    {
        return collect($results)->where('is_correct', true)->count();
    }

    // ============================================
    // PRIVATE METHODS - Points & Leaderboard
    // ============================================

    private function handleUserPoints($user, Quiz $quiz, int $score, int $totalQuestions, ?int $timeSpent): ?array
    {
        if (!$user) {
            Log::info('No user authenticated, skipping points calculation');
            return null;
        }

        try {
            $this->logPointsCalculationStart($user, $quiz, $score, $totalQuestions, $timeSpent);

            $pointsData = $this->pointsService->calculatePoints($quiz, $score, $totalQuestions, $timeSpent);
            $quizAttempt = $this->pointsService->awardPoints($user, $quiz, $pointsData);

            $this->updateUserRanking($user, $quiz);
            $this->logSuccessfulCompletion($user, $quiz, $score, $totalQuestions, $pointsData);

            $pointsData['quiz_attempt_id'] = $quizAttempt?->id;
            return $pointsData;

        } catch (\Exception $e) {
            $this->logPointsError($user, $quiz, $e);
            return null;
        }
    }

    private function updateUserRanking($user, Quiz $quiz): void
    {
        try {
            $this->leaderboardService->updateUserRanking($user->id);
            Log::info('User ranking updated after quiz completion', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user ranking after quiz completion', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ============================================
    // PRIVATE METHODS - Response Building
    // ============================================

    private function buildSubmissionResponse(
        Quiz $quiz,
        int $score,
        int $totalQuestions,
        array $results,
        ?array $pointsData,
        float $processingTime,
        ?int $timeSpent
    ): array {
        $response = [
            "score" => $score,
            "total" => $totalQuestions,
            "percentage" => round(($score / $totalQuestions) * 100, 2),
            "results" => $results,
            "quiz_info" => $this->buildQuizInfo($quiz, $score),
            "performance" => $this->buildPerformanceInfo($processingTime, $timeSpent),
        ];

        if ($pointsData) {
            $response["points"] = $pointsData;
            if (isset($pointsData['quiz_attempt_id'])) {
                $response["quiz_attempt_id"] = $pointsData['quiz_attempt_id'];
            }
        }

        return $response;
    }

    private function buildQuizInfo(Quiz $quiz, int $score): array
    {
        return [
            "id" => $quiz->id,
            "title" => $quiz->title,
            "level" => $quiz->level?->name,
            "pass_score" => $quiz->pass_score,
            "passed" => $quiz->pass_score ? $score >= $quiz->pass_score : null,
        ];
    }

    private function buildPerformanceInfo(float $processingTime, ?int $timeSpent): array
    {
        return [
            "processing_time" => round($processingTime * 1000, 2) . 'ms',
            "time_spent" => $timeSpent ? "{$timeSpent}s" : null,
        ];
    }

    // ============================================
    // PRIVATE METHODS - Data Preparation
    // ============================================

    private function prepareQuizData(array $data): array
    {
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (isset($data['duration']) && in_array($data['duration'], [0, '0', null], true)) {
            $data['duration'] = null;
        }

        return $data;
    }

    private function safelyIndexQuiz(Quiz $quiz): void
    {
        try {
            if ($quiz->shouldBeSearchable()) {
                $quiz->searchable();
            }
        } catch (\Exception $e) {
            Log::warning("Quiz operation successful but Elasticsearch indexing failed: " . $e->getMessage(), [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title
            ]);
        }
    }

    // ============================================
    // PRIVATE METHODS - Logging
    // ============================================

    private function logPointsCalculationStart($user, Quiz $quiz, int $score, int $totalQuestions, ?int $timeSpent): void
    {
        Log::info('User authenticated for points calculation', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'time_spent' => $timeSpent,
            'points_service_exists' => !is_null($this->pointsService)
        ]);
    }

    private function logSuccessfulCompletion($user, Quiz $quiz, int $score, int $totalQuestions, array $pointsData): void
    {
        Log::info('Quiz completed with points attribution', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'points_awarded' => $pointsData['total_points'] ?? 0,
        ]);
    }

    private function logPointsError($user, Quiz $quiz, \Exception $e): void
    {
        Log::error('Error calculating/awarding points', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
