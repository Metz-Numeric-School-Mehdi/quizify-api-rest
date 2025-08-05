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
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
