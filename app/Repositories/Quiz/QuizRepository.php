<?php

namespace App\Repositories\Quiz;

use App\Models\Quiz;
use App\Components\Repository;
use App\Models\QuestionResponse;

class QuizRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Quiz());
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
            "total" => count($quiz->questions),
            "results" => $results,
        ];
    }
}
