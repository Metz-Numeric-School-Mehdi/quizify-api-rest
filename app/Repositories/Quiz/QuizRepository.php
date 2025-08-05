<?php

namespace App\Repositories\Quiz;

use App\Models\Quiz;
use App\Components\Repository;
use App\Models\QuestionResponse;
use App\Services\ElasticsearchService;
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
     * QuizRepository constructor.
     *
     * @param ElasticsearchService|null $elasticsearchService
     */
    public function __construct(?ElasticsearchService $elasticsearchService = null)
    {
        parent::__construct(new Quiz());
        $this->elasticsearchService = $elasticsearchService ?? app(ElasticsearchService::class);
    }

    public function submit($user, $quizId, array $responses)
    {
        $quiz = Quiz::with("questions.answers")->findOrFail($quizId);
        $userId = $user ? $user->id : null;
        $score = 0;
        $results = [];

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
                "user_id" => $userId, // null si guest
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

        return [
            "score" => $score,
            "total" => count($responses),
            "results" => $results,
        ];
    }

    /**
     * Store a newly created quiz with safe Elasticsearch indexing.
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Ensure slug is created if not provided
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Create quiz with Scout disabled to avoid automatic indexing
        $quiz = Quiz::withoutSyncingToSearch(function () use ($data) {
            return parent::store($data);
        });

        // Try to index manually after successful creation
        try {
            $this->safelyIndexQuiz($quiz);
        } catch (\Exception $e) {
            Log::warning("Quiz created successfully but Elasticsearch indexing failed: " . $e->getMessage());
        }

        return $quiz;
    }

    /**
     * Update a quiz with safe Elasticsearch indexing.
     *
     * @param array $data
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($data, $id): \Illuminate\Database\Eloquent\Model
    {
        // Ensure slug is created if not provided but title is changed
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Update quiz with Scout disabled to avoid automatic indexing
        $quiz = Quiz::withoutSyncingToSearch(function () use ($data, $id) {
            return parent::update($data, $id);
        });

        // Try to index manually after successful update
        try {
            $this->safelyIndexQuiz($quiz);
        } catch (\Exception $e) {
            Log::warning("Quiz updated successfully but Elasticsearch indexing failed: " . $e->getMessage());
        }

        return $quiz;
    }

    /**
     * Safely indexes a quiz in Elasticsearch.
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
