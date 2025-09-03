<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaderboardService
{
    /**
     * Update the ranking of all users based on their scores
     * @return bool True if the update was successful
     */
    public function updateAllRankings(): bool
    {
        try {
            $users = User::select([
                "users.id",
                DB::raw("COALESCE(SUM(scores.score), 0) as total_score"),
            ])
                ->leftJoin("scores", "users.id", "=", "scores.user_id")
                ->groupBy("users.id")
                ->orderBy("total_score", "desc")
                ->get();

            $rank = 1;
            foreach ($users as $user) {
                User::where("id", $user->id)->update(["ranking" => $rank]);
                $rank++;
            }
            Log::info("User rankings updated successfully from controller function.");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update user rankings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update the ranking of a specific user based on their new score
     * This is more efficient than updating all rankings
     *
     * @param int $userId The ID of the user to update
     * @return bool True if the update was successful
     */
    public function updateUserRanking(int $userId): bool
    {
        try {
            if (!User::where("id", $userId)->exists()) {
                Log::warning("Attempted to update ranking for non-existent user ID: " . $userId);
                return false;
            }

            $userScore = DB::table("scores")->where("user_id", $userId)->sum("score") ?? 0;

            $higherScoreCount = DB::table("users")
                ->leftJoin("scores", "users.id", "=", "scores.user_id")
                ->select("users.id")
                ->groupBy("users.id")
                ->havingRaw("COALESCE(SUM(scores.score), 0) > ?", [$userScore])
                ->count();

            $newRank = $higherScoreCount + 1;

            User::where("id", $userId)->update(["ranking" => $newRank]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update user ranking: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get global leaderboard data with pagination
     *
     * @param int $limit Number of users per page
     * @param int $page Page number
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getGlobalLeaderboard(int $limit = 50, int $page = 1, string $order = "desc")
    {
        $order = strtolower($order) === "asc" ? "asc" : "desc";
        return User::select([
            "users.id",
            "users.username",
            "users.firstname",
            "users.lastname",
            "users.avatar",
            "users.ranking",
            DB::raw("COALESCE(SUM(scores.score), 0) as total_score"),
            DB::raw("COUNT(DISTINCT scores.quiz_id) as quizzes_completed"),
        ])
            ->leftJoin("scores", "users.id", "=", "scores.user_id")
            ->groupBy(
                "users.id",
                "users.username",
                "users.firstname",
                "users.lastname",
                "users.avatar",
                "users.ranking",
            )
            ->orderBy("total_score", $order)
            ->orderBy("quizzes_completed", $order)
            ->orderBy("users.username", "asc")
            ->paginate($limit, ["*"], "page", $page);
    }

    /**
     * Get leaderboard data filtered by category
     *
     * @param int $categoryId Category ID to filter by
     * @param int $limit Number of users per page
     * @param int $page Page number
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCategoryLeaderboard(
        int $categoryId,
        int $limit = 50,
        int $page = 1,
        string $order = "desc",
    ) {
        $order = strtolower($order) === "asc" ? "asc" : "desc";
        return User::select([
            "users.id",
            "users.username",
            "users.firstname",
            "users.lastname",
            "users.avatar",
            "users.ranking",
            DB::raw("COALESCE(SUM(scores.score), 0) as total_score"),
            DB::raw("COUNT(DISTINCT scores.quiz_id) as quizzes_completed"),
        ])
            ->leftJoin("scores", "users.id", "=", "scores.user_id")
            ->leftJoin("quizzes", "scores.quiz_id", "=", "quizzes.id")
            ->where("quizzes.category_id", $categoryId)
            ->groupBy(
                "users.id",
                "users.username",
                "users.firstname",
                "users.lastname",
                "users.avatar",
                "users.ranking",
            )
            ->orderBy("total_score", $order)
            ->orderBy("quizzes_completed", $order)
            ->orderBy("users.username", "asc")
            ->paginate($limit, ["*"], "page", $page);
    }

    /**
     * Get leaderboard data filtered by organization
     *
     * @param int $organizationId Organization ID to filter by
     * @param int $limit Number of users per page
     * @param int $page Page number
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOrganizationLeaderboard(
        int $organizationId,
        int $limit = 50,
        int $page = 1,
        string $order = "desc",
    ) {
        $order = strtolower($order) === "asc" ? "asc" : "desc";
        return User::select([
            "users.id",
            "users.username",
            "users.firstname",
            "users.lastname",
            "users.avatar",
            "users.ranking",
            "teams.name as team_name",
            DB::raw("COALESCE(SUM(scores.score), 0) as total_score"),
            DB::raw("COUNT(DISTINCT scores.quiz_id) as quizzes_completed"),
        ])
            ->leftJoin("scores", "users.id", "=", "scores.user_id")
            ->leftJoin("teams", "users.team_id", "=", "teams.id")
            ->where("users.organization_id", $organizationId)
            ->groupBy(
                "users.id",
                "users.username",
                "users.firstname",
                "users.lastname",
                "users.avatar",
                "users.ranking",
                "teams.name",
            )
            ->orderBy("total_score", $order)
            ->orderBy("quizzes_completed", $order)
            ->orderBy("users.username", "asc")
            ->paginate($limit, ["*"], "page", $page);
    }
}
