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
    /**
     * The Elasticsearch service instance.
     *
     * @var ElasticsearchService|null
     */
    protected $elasticsearchService;

    /**
     * The Points calculation service instance.
     *
     * @var PointsCalculationService
     */
    protected $pointsService;

    /**
     * The Leaderboard service instance.
     *
     * @var LeaderboardService
     */
    protected $leaderboardService;

    /**
     * QuizRepository constructor.
     *
     * @param ElasticsearchService|null $elasticsearchService
     * @param PointsCalculationService|null $pointsService
     * @param LeaderboardService|null $leaderboardService
     */
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
     * The relations to eager load on every query.
     *
     * @var array
     *
     */
    protected $withRelations = [
        'questions.answers'
    ];

    /**
     * Get all quizzes with their related questions and answers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return $this->model->with($this->withRelations)->get();
    }

    /**
     * Retrieve a specific quiz with its related questions and answers.
     *
     * @param int $id
     * @return Model
     */
    public function show($id)
    {
        return $this->model->with($this->withRelations)->findOrFail($id);
    }

    /**
     * Submit user responses for a quiz and calculate the score with points attribution.
     *
     * @param \App\Models\User|null $user
     * @param int $quizId
     * @param array $responses
     * @param int|null $timeSpent Time spent in seconds
     * @return array
     * @throws \Exception
     */
    public function submit($user, $quizId, array $responses, ?int $timeSpent = null)
    {
        $quiz = Quiz::with("questions.answers", "level")->findOrFail($quizId);
        $score = 0;
        $results = [];
        $startTime = microtime(true);

        foreach ($responses as $response) {
            $question = $quiz->questions->where("id", $response["question_id"])->first();
            if (!$question) {
                throw new \Exception("Question non trouvée: " . $response["question_id"]);
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

            QuestionResponse::create([
                "quiz_id" => $quiz->id,
                "question_id" => $question->id,
                "answer_id" => $response["answer_id"] ?? null,
                "user_answer" => $response["user_answer"] ?? null,
                "is_correct" => $isCorrect,
                "user_id" => $user?->id,
            ]);

            $results[] = [
                "question_id" => $question->id,
                "is_correct" => $isCorrect,
            ];

            if ($isCorrect) {
                $score++;
            }
        }

        $totalQuestions = count($responses);
        $processingTime = microtime(true) - $startTime;

        $pointsData = null;
        $quizAttempt = null;

        if ($user) {
            Log::info('User authenticated for points calculation', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => $score,
                'total_questions' => $totalQuestions,
                'time_spent' => $timeSpent,
                'points_service_exists' => !is_null($this->pointsService)
            ]);

            try {
                $pointsData = $this->pointsService->calculatePoints(
                    $quiz,
                    $score,
                    $totalQuestions,
                    $timeSpent
                );

                Log::info('Points calculated successfully', [
                    'user_id' => $user->id,
                    'quiz_id' => $quiz->id,
                    'points_data' => $pointsData
                ]);

                $quizAttempt = $this->pointsService->awardPoints($user, $quiz, $pointsData);

                // Update user ranking after points are awarded
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

                Log::info('Quiz completed with points attribution', [
                    'user_id' => $user->id,
                    'quiz_id' => $quiz->id,
                    'score' => $score,
                    'total_questions' => $totalQuestions,
                    'points_awarded' => $pointsData['total_points'],
                    'processing_time' => $processingTime
                ]);
            } catch (\Exception $e) {
                Log::error('Error calculating/awarding points', [
                    'user_id' => $user->id,
                    'quiz_id' => $quiz->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::info('No user authenticated, skipping points calculation');
        }

        // Réponse enrichie
        $response = [
            "score" => $score,
            "total" => $totalQuestions,
            "percentage" => round(($score / $totalQuestions) * 100, 2),
            "results" => $results,
            "quiz_info" => [
                "id" => $quiz->id,
                "title" => $quiz->title,
                "level" => $quiz->level?->name,
                "pass_score" => $quiz->pass_score,
                "passed" => $quiz->pass_score ? $score >= $quiz->pass_score : null,
            ],
            "performance" => [
                "processing_time" => round($processingTime * 1000, 2) . 'ms',
                "time_spent" => $timeSpent ? "{$timeSpent}s" : null,
            ]
        ];

        if ($pointsData) {
            $response["points"] = $pointsData;
            $response["quiz_attempt_id"] = $quizAttempt?->id;
        }

        return $response;
    }

    /**
     * Store a newly created quiz with safe Elasticsearch indexing.
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (!isset($data['duration']) || $data['duration'] === 0 || $data['duration'] === '0') {
            $data['duration'] = null;
        }

        $quiz = Quiz::withoutSyncingToSearch(function () use ($data) {
            return parent::store($data);
        });

        try {
            $this->safelyIndexQuiz($quiz);
        } catch (\Exception $e) {
            Log::warning("Quiz created successfully but Elasticsearch indexing failed: " . $e->getMessage());
        }

        $quiz->load('level', 'category', 'tags');

        return $quiz;
    }

    /**
     * Update a quiz with safe Elasticsearch indexing.
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update($data, $id): Model
    {
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Gérer la duration : si 0 ou non définie, mettre null pour temps infini
        if (isset($data['duration']) && ($data['duration'] === 0 || $data['duration'] === '0')) {
            $data['duration'] = null;
        }

        $quiz = Quiz::withoutSyncingToSearch(function () use ($data, $id) {
            return parent::update($data, $id);
        });

        try {
            $this->safelyIndexQuiz($quiz);
        } catch (\Exception $e) {
            Log::warning("Quiz updated successfully but Elasticsearch indexing failed: " . $e->getMessage());
        }

        return $quiz;
    }

    /**
     * Safely index a quiz in Elasticsearch.
     *
     * @param Quiz $quiz
     * @return bool
     */
    protected function safelyIndexQuiz(Quiz $quiz): bool
    {
        try {
            if ($quiz->shouldBeSearchable()) {
                $quiz->searchable();
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to index quiz in Elasticsearch: " . $e->getMessage(), [
                'id' => $quiz->id,
                'title' => $quiz->title
            ]);
        }

        return false;
    }
}

