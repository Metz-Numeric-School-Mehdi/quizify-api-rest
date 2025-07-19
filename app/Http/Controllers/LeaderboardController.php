<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Score;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * The leaderboard service instance.
     *
     * @var LeaderboardService
     */
    protected $leaderboardService;

    /**
     * Create a new controller instance.
     *
     * @param LeaderboardService $leaderboardService
     * @return void
     */
    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * Get the global leaderboard of users ranked by their scores
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 50);
        $page = $request->query('page', 1);
        $order = $request->query('order', 'desc');

        $leaderboard = $this->leaderboardService->getGlobalLeaderboard($limit, $page, $order);

        if (isset($leaderboard['data']) && is_array($leaderboard['data'])) {
            $leaderboard['data'] = array_map(function ($user, $index) use ($leaderboard, $order) {
                $user['total_score'] = isset($user['total_score']) ? (int)$user['total_score'] : 0;
                $user['ranking'] = $user['ranking'] === null ? 0 : $user['ranking'];
                $user['quizzes_completed'] = isset($user['quizzes_completed']) ? (int)$user['quizzes_completed'] : 0;
                $user['position'] = $order === 'asc' ? count($leaderboard['data']) - $index : $index + 1;
                return $user;
            }, $leaderboard['data'], array_keys($leaderboard['data']));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Leaderboard retrieved successfully',
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get the leaderboard filtered by quiz category
     *
     * @param Request $request
     * @param int $categoryId
     * @return JsonResponse
     */
    public function byCategory(Request $request, int $categoryId): JsonResponse
    {
        $limit = $request->query('limit', 50);
        $page = $request->query('page', 1);
        $order = $request->query('order', 'desc');

        $leaderboard = $this->leaderboardService->getCategoryLeaderboard($categoryId, $limit, $page, $order);

        if (isset($leaderboard['data']) && is_array($leaderboard['data'])) {
            $leaderboard['data'] = array_map(function ($user, $index) use ($leaderboard, $order) {
                $user['total_score'] = isset($user['total_score']) ? (int)$user['total_score'] : 0;
                $user['ranking'] = $user['ranking'] === null ? 0 : $user['ranking'];
                $user['quizzes_completed'] = isset($user['quizzes_completed']) ? (int)$user['quizzes_completed'] : 0;
                $user['position'] = $order === 'asc' ? count($leaderboard['data']) - $index : $index + 1;
                return $user;
            }, $leaderboard['data'], array_keys($leaderboard['data']));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Category leaderboard retrieved successfully',
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get the leaderboard for a specific organization
     *
     * @param Request $request
     * @param int $organizationId
     * @return JsonResponse
     */
    public function byOrganization(Request $request, int $organizationId): JsonResponse
    {
        $limit = $request->query('limit', 50);
        $page = $request->query('page', 1);
        $order = $request->query('order', 'desc');

        $leaderboard = $this->leaderboardService->getOrganizationLeaderboard($organizationId, $limit, $page, $order);

        if (isset($leaderboard['data']) && is_array($leaderboard['data'])) {
            $leaderboard['data'] = array_map(function ($user, $index) use ($leaderboard, $order) {
                $user['total_score'] = isset($user['total_score']) ? (int)$user['total_score'] : 0;
                $user['ranking'] = $user['ranking'] === null ? 0 : $user['ranking'];
                $user['quizzes_completed'] = isset($user['quizzes_completed']) ? (int)$user['quizzes_completed'] : 0;
                $user['position'] = $order === 'asc' ? count($leaderboard['data']) - $index : $index + 1;
                return $user;
            }, $leaderboard['data'], array_keys($leaderboard['data']));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Organization leaderboard retrieved successfully',
            'data' => $leaderboard,
        ]);
    }

    /**
     * Update user rankings based on their scores
     * This could be called via a scheduled job
     *
     * @return JsonResponse
     */
    public function updateRankings(): JsonResponse
    {
        $success = $this->leaderboardService->updateAllRankings();

        if ($success) {
            return response()->json([
                'status' => 'success',
                'message' => 'User rankings updated successfully',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user rankings',
            ], 500);
        }
    }
}
