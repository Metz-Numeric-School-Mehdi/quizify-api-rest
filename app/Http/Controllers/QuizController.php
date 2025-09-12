<?php

namespace App\Http\Controllers;

use App\Repositories\Quiz\QuizRepository;
use App\Services\QuizService;
use App\Http\Controllers\CRUDController;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuizController extends CRUDController
{
    /**
     * The repository instance.
     *
     * @var QuizRepository
     */
    protected $repository;

    /**
     * The quiz service instance.
     *
     * @var QuizService
     */
    protected QuizService $quizService;

    /**
     * Create a new controller instance.
     *
     * @param QuizRepository $repository
     * @param QuizService $quizService
     */
    public function __construct(QuizRepository $repository, QuizService $quizService)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->quizService = $quizService;
    }

    /**
     * Handle quiz submission through the QuizService.
     *
     * @param Request $request
     * @param int $quizId
     * @return JsonResponse
     */
    public function submit(Request $request, $quizId): JsonResponse
    {
        try {
            $result = $this->quizService->submitQuiz($request, $quizId);
            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Données de soumission invalides",
                "errors" => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search quizzes through the QuizService.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $result = $this->quizService->searchQuizzes($request);
            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Paramètres de recherche invalides",
                "errors" => $e->errors()
            ], 422);
        } catch (Exception $e) {
            if ($e->getMessage() === 'Aucun quiz trouvé pour cette recherche.') {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return response()->json([
                'message' => 'Erreur lors de la recherche: ' . $e->getMessage()
            ], 500);
        }
    }
}
