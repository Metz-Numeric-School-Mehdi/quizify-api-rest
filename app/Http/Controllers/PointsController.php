<?php

namespace App\Http\Controllers;

use App\Services\PointsCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PointsController extends Controller
{
    /**
     * The points calculation service instance.
     *
     * @var PointsCalculationService
     */
    protected $pointsService;

    /**
     * PointsController constructor.
     *
     * @param PointsCalculationService $pointsService
     */
    public function __construct(PointsCalculationService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Get user's total points
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserPoints(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $totalPoints = $this->pointsService->getUserTotalPoints($user);

        return response()->json([
            'user_id' => $user->id,
            'username' => $user->username,
            'total_points' => $totalPoints
        ]);
    }

    /**
     * Get user's points by category
     *
     * @param Request $request
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getUserCategoryPoints(Request $request, int $categoryId): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $categoryPoints = $this->pointsService->getUserCategoryPoints($user, $categoryId);

        return response()->json([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'category_points' => $categoryPoints
        ]);
    }

    /**
     * Get global points leaderboard
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLeaderboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $limit = $validated['limit'] ?? 10;
        $leaderboard = $this->pointsService->getPointsLeaderboard($limit);

        return response()->json([
            'leaderboard' => $leaderboard,
            'limit' => $limit,
            'total_users' => $leaderboard->count()
        ]);
    }

    /**
     * Get points configuration
     *
     * @return JsonResponse
     */
    public function getPointsConfig(): JsonResponse
    {
        return response()->json([
            'points_system' => PointsCalculationService::POINTS_CONFIG,
            'description' => [
                'base_points' => 'Points accordés par bonne réponse',
                'level_multipliers' => 'Multiplicateurs selon le niveau de difficulté',
                'bonus_thresholds' => 'Bonus accordés selon le pourcentage de réussite',
                'time_bonus' => 'Bonus de vitesse si quiz terminé rapidement'
            ]
        ]);
    }
}
