<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderingQuestionController extends Controller
{
    /**
     * Create a new ordering question with answers that need to be ordered.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createOrderingQuestion(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'content' => 'required|string|max:1000',
            'answers' => 'required|array|min:2',
            'answers.*.content' => 'required|string|max:500',
            'answers.*.order_position' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $orderingType = QuestionType::where('name', 'Remise dans l\'ordre')->firstOrFail();

            $question = Question::create([
                'quiz_id' => $request->quiz_id,
                'content' => $request->input('content'),
                'question_type_id' => $orderingType->id
            ]);

            foreach ($request->answers as $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'content' => $answerData['content'],
                    'is_correct' => true,
                    'order_position' => $answerData['order_position']
                ]);
            }

            DB::commit();

            $question->load(['answers' => function($query) {
                $query->orderBy('order_position');
            }, 'questionType']);

            return response()->json([
                'message' => 'Question de remise dans l\'ordre créée avec succès',
                'data' => $question
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de la question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get an ordering question with shuffled answers for display to user.
     *
     * @param int $questionId
     * @return JsonResponse
     */
    public function getOrderingQuestion(int $questionId): JsonResponse
    {
        try {
            $question = Question::with(['questionType', 'answers'])->findOrFail($questionId);

            $orderingType = QuestionType::where('name', 'Remise dans l\'ordre')->firstOrFail();

            if ($question->question_type_id !== $orderingType->id) {
                return response()->json([
                    'message' => 'Cette question n\'est pas de type remise dans l\'ordre'
                ], 400);
            }

            $shuffledAnswers = $question->answers->map(function($answer) {
                return [
                    'id' => $answer->id,
                    'content' => $answer->content
                ];
            })->shuffle()->values();

            return response()->json([
                'question' => [
                    'id' => $question->id,
                    'content' => $question->content,
                    'type' => $question->questionType->name,
                    'answers' => $shuffledAnswers
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Question non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Submit and validate ordering question response.
     *
     * @param Request $request
     * @param int $questionId
     * @return JsonResponse
     */
    public function submitOrderingResponse(Request $request, int $questionId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_order' => 'required|array|min:2',
            'user_order.*' => 'required|integer|exists:answers,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $question = Question::with(['answers', 'questionType'])->findOrFail($questionId);

            $orderingType = QuestionType::where('name', 'Remise dans l\'ordre')->firstOrFail();

            if ($question->question_type_id !== $orderingType->id) {
                return response()->json([
                    'message' => 'Cette question n\'est pas de type remise dans l\'ordre'
                ], 400);
            }

            $correctOrder = $question->answers()
                ->orderBy('order_position')
                ->pluck('id')
                ->toArray();

            $userOrder = $request->user_order;

            if (count($correctOrder) !== count($userOrder)) {
                return response()->json([
                    'message' => 'Le nombre d\'éléments ne correspond pas'
                ], 400);
            }

            $answersWithPosition = $question->answers->keyBy('id');
            $isCorrect = true;
            $score = 0;
            $maxScore = count($correctOrder);
            $feedback = [];

            for ($i = 0; $i < count($userOrder); $i++) {
                $userAnswerId = $userOrder[$i];
                $correctAnswerId = $correctOrder[$i];
                $isPositionCorrect = $userAnswerId == $correctAnswerId;

                if ($isPositionCorrect) {
                    $score++;
                } else {
                    $isCorrect = false;
                }

                $feedback[] = [
                    'position' => $i + 1,
                    'user_answer' => [
                        'id' => $userAnswerId,
                        'content' => $answersWithPosition[$userAnswerId]->content
                    ],
                    'correct_answer' => [
                        'id' => $correctAnswerId,
                        'content' => $answersWithPosition[$correctAnswerId]->content
                    ],
                    'is_correct' => $isPositionCorrect
                ];
            }

            $correctOrderWithContent = collect($correctOrder)->map(function($answerId) use ($answersWithPosition) {
                return [
                    'id' => $answerId,
                    'content' => $answersWithPosition[$answerId]->content,
                    'position' => $answersWithPosition[$answerId]->order_position
                ];
            });

            return response()->json([
                'message' => $isCorrect ? 'Parfait ! Ordre correct' : 'Ordre incorrect',
                'result' => [
                    'is_correct' => $isCorrect,
                    'score' => $score,
                    'max_score' => $maxScore,
                    'percentage' => round(($score / $maxScore) * 100, 2),
                    'correct_order' => $correctOrderWithContent,
                    'feedback' => $feedback
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la soumission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all ordering questions for a specific quiz.
     *
     * @param int $quizId
     * @return JsonResponse
     */
    public function getOrderingQuestionsByQuiz(int $quizId): JsonResponse
    {
        try {
            $orderingType = QuestionType::where('name', 'Remise dans l\'ordre')->firstOrFail();

            $questions = Question::where('quiz_id', $quizId)
                ->where('question_type_id', $orderingType->id)
                ->with(['answers' => function($query) {
                    $query->orderBy('order_position');
                }, 'questionType'])
                ->get();

            return response()->json([
                'message' => 'Questions de remise dans l\'ordre récupérées avec succès',
                'data' => $questions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an ordering question and its answers.
     *
     * @param Request $request
     * @param int $questionId
     * @return JsonResponse
     */
    public function updateOrderingQuestion(Request $request, int $questionId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|required|string|max:1000',
            'answers' => 'sometimes|required|array|min:2',
            'answers.*.id' => 'sometimes|integer|exists:answers,id',
            'answers.*.content' => 'required|string|max:500',
            'answers.*.order_position' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $question = Question::with(['answers', 'questionType'])->findOrFail($questionId);

            $orderingType = QuestionType::where('name', 'Remise dans l\'ordre')->firstOrFail();

            if ($question->question_type_id !== $orderingType->id) {
                return response()->json([
                    'message' => 'Cette question n\'est pas de type remise dans l\'ordre'
                ], 400);
            }

            if ($request->has('content')) {
                $question->update(['content' => $request->input('content')]);
            }

            if ($request->has('answers')) {
                $question->answers()->delete();

                foreach ($request->answers as $answerData) {
                    Answer::create([
                        'question_id' => $question->id,
                        'content' => $answerData['content'],
                        'is_correct' => true,
                        'order_position' => $answerData['order_position']
                    ]);
                }
            }

            DB::commit();

            $question->load(['answers' => function($query) {
                $query->orderBy('order_position');
            }, 'questionType']);

            return response()->json([
                'message' => 'Question de remise dans l\'ordre mise à jour avec succès',
                'data' => $question
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la question',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
