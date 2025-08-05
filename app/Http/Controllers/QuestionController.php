<?php

namespace App\Http\Controllers;

use App\Repositories\Question\QuestionRepository;
use App\Http\Controllers\CRUDController;

class QuestionController extends CRUDController
{
    /**
     * Create a new controller instance.
     *
     * @param QuestionRepository $repository
     */
    public function __construct(QuestionRepository $repository)
    {
        parent::__construct($repository);
    }

    // /**
    //  * Display a listing of questions with resources.
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function index(): JsonResponse
    // {
    //     $questions = $this->repository->index();

    //     if ($questions->isEmpty()) {
    //         return response()->json([
    //             'message' => 'Aucune question trouvée',
    //         ], 404);
    //     }

    //     return response()->json(QuestionResource::collection($questions));
    // }

    // /**
    //  * Display the specified question with resource.
    //  *
    //  * @param int $id
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function show($id): JsonResponse
    // {
    //     try {
    //         $question = $this->repository->show($id);
    //         return response()->json(new QuestionResource($question));
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Question non trouvée',
    //         ], 404);
    //     }
    // }

    // /**
    //  * Store a newly created question in storage.
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function store(Request $request): JsonResponse
    // {
    //     // Use parent store method which handles validation via RuleStrategy
    //     $response = parent::store($request);

    //     // If successful, return the question with QuestionResource
    //     if ($response->getStatusCode() === 201) {
    //         $data = json_decode($response->getContent(), true);
    //         $question = Question::with('answers', 'questionType', 'quiz')->find($data['data']['id']);

    //         return response()->json([
    //             'message' => $data['message'],
    //             'data' => new QuestionResource($question),
    //         ], 201);
    //     }

    //     return $response;
    // }

    // /**
    //  * Update the specified question in storage.
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @param int $id
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function update(Request $request, int $id): JsonResponse
    // {
    //     try {
    //         // Check if user can modify this question
    //         if (!$this->canUserModifyQuestion($id, $request->user()->id)) {
    //             return response()->json([
    //                 "message" => "Vous n'êtes pas autorisé à modifier cette question.",
    //             ], 403);
    //         }

    //         // Use parent update method which handles validation via RuleStrategy
    //         return parent::update($request, $id);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "message" => "Erreur lors de la mise à jour de la question.",
    //             "error" => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Remove the specified question from storage.
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @param int $id
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function destroy(Request $request, int $id): JsonResponse
    // {
    //     try {
    //         // Check if user can modify this question
    //         if (!$this->canUserModifyQuestion($id, $request->user()->id)) {
    //             return response()->json([
    //                 "message" => "Vous n'êtes pas autorisé à supprimer cette question.",
    //             ], 403);
    //         }

    //         // Use parent destroy method
    //         return parent::destroy($request, $id);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "message" => "Erreur lors de la suppression de la question.",
    //             "error" => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Get questions by quiz ID.
    //  *
    //  * @param int $quizId
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function getByQuiz(int $quizId): JsonResponse
    // {
    //     try {
    //         $questions = Question::with('answers', 'questionType')
    //             ->where('quiz_id', $quizId)
    //             ->get();

    //         if ($questions->isEmpty()) {
    //             return response()->json([
    //                 'message' => 'Aucune question trouvée pour ce quiz',
    //             ], 404);
    //         }

    //         return response()->json(QuestionResource::collection($questions));
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Erreur lors de la récupération des questions',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Check if user can modify the question.
    //  *
    //  * @param int $questionId
    //  * @param int $userId
    //  * @return bool
    //  */
    // private function canUserModifyQuestion(int $questionId, int $userId): bool
    // {
    //     $question = Question::with('quiz')->find($questionId);

    //     if (!$question || !$question->quiz) {
    //         return false;
    //     }

    //     return $question->quiz->user_id === $userId;
    // }
}
