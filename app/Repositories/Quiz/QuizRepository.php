<?php

namespace App\Repositories\Quiz;

use App\Models\Quiz;
use App\Components\Repository;

class QuizRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Quiz());
    }

    // public function submit(Request $request, $quizId)
    // {
    //     try {
    //         $validated = $request->validate([
    //             "responses" => "required|array",
    //             "responses.*.question_id" => "required|integer|exists:questions,id",
    //             "responses.*.answer_id" => "nullable|integer|exists:answers,id",
    //             "responses.*.user_answer" => "nullable|string",
    //         ]);

    //         $quiz = Quiz::with("questions.answers")->findOrFail($quizId);
    //         dd($quiz);
    //         $user = $request->user();
    //         if (!$user) {
    //             return response()->json(["error" => "Utilisateur non authentifié"], 401);
    //         }
    //         $userId = $user->id;
    //         $score = 0;
    //         $results = [];

    //         foreach ($validated["responses"] as $response) {
    //             $question = $quiz->questions->where("id", $response["question_id"])->first();
    //             if (!$question) {
    //                 return response()->json(
    //                     ["error" => "Question non trouvée: " . $response["question_id"]],
    //                     404,
    //                 );
    //             }
    //             $correctAnswer = $question->answers->where("is_correct", true)->first();
    //             $isCorrect = false;

    //             if (isset($response["answer_id"])) {
    //                 $isCorrect = $correctAnswer && $correctAnswer->id == $response["answer_id"];
    //             } elseif (isset($response["user_answer"])) {
    //                 $isCorrect =
    //                     strtolower(trim($correctAnswer->content ?? "")) ===
    //                     strtolower(trim($response["user_answer"]));
    //             }

    //             QuestionResponse::create([
    //                 "quiz_id" => $quiz->id,
    //                 "user_id" => $userId,
    //                 "question_id" => $question->id,
    //                 "answer_id" => $response["answer_id"] ?? null,
    //                 "user_answer" => $response["user_answer"] ?? null,
    //                 "is_correct" => $isCorrect,
    //             ]);

    //             $results[] = [
    //                 "question_id" => $question->id,
    //                 "is_correct" => $isCorrect,
    //             ];
    //             if ($isCorrect) {
    //                 $score++;
    //             }
    //         }

    //         return response()->json([
    //             "score" => $score,
    //             "total" => count($quiz->questions),
    //             "results" => $results,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(
    //             [
    //                 "error" => $e->getMessage(),
    //                 "trace" => $e->getTraceAsString(),
    //             ],
    //             500,
    //         );
    //     }
    // }
}
