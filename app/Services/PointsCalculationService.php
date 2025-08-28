<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\User;
use App\Models\Score;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointsCalculationService
{
    /**
     * Points configuration per difficulty level
     */
    const POINTS_CONFIG = [
        'base_points' => 10,
        'level_multipliers' => [
            1 => 1.0,
            2 => 1.5,
            3 => 2.0,
            4 => 3.0,
        ],
        'bonus_thresholds' => [
            100 => 50,
            90  => 30,
            80  => 20,
            70  => 10,
        ],
        'time_bonus' => [
            'enabled' => true,
            'max_bonus' => 25,
            'threshold_percent' => 50,
        ]
    ];

    /**
     * Calculate points for a quiz attempt
     *
     * @param Quiz $quiz
     * @param int $correctAnswers
     * @param int $totalQuestions
     * @param int|null $timeSpent Temps passé en secondes
     * @return array
     */
    public function calculatePoints(Quiz $quiz, int $correctAnswers, int $totalQuestions, ?int $timeSpent = null): array
    {
        $basePoints = $correctAnswers * self::POINTS_CONFIG['base_points'];

        $levelMultiplier = $this->getLevelMultiplier($quiz->level_id);
        $levelPoints = $basePoints * $levelMultiplier;

        $performanceBonus = $this->calculatePerformanceBonus($correctAnswers, $totalQuestions);

        $speedBonus = $this->calculateSpeedBonus($quiz, $timeSpent);

        $totalPoints = (int) ($levelPoints + $performanceBonus + $speedBonus);

        return [
            'base_points' => $basePoints,
            'level_multiplier' => $levelMultiplier,
            'level_points' => (int) $levelPoints,
            'performance_bonus' => $performanceBonus,
            'speed_bonus' => $speedBonus,
            'total_points' => $totalPoints,
            'breakdown' => [
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'success_rate' => round(($correctAnswers / $totalQuestions) * 100, 2),
                'quiz_level' => $quiz->level_id,
                'time_spent' => $timeSpent,
                'quiz_duration' => $quiz->duration,
            ]
        ];
    }

    /**
     * Award points to user and create records
     *
     * @param User $user
     * @param Quiz $quiz
     * @param array $pointsData
     * @return QuizAttempt
     */
    public function awardPoints(User $user, Quiz $quiz, array $pointsData): QuizAttempt
    {
        return DB::transaction(function () use ($user, $quiz, $pointsData) {
            $quizAttempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => $pointsData['breakdown']['correct_answers'],
                'max_score' => $pointsData['breakdown']['total_questions'],
            ]);

            Score::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => $pointsData['total_points'],
            ]);

            Log::info('Points awarded to user', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'points_awarded' => $pointsData['total_points'],
                'breakdown' => $pointsData
            ]);

            return $quizAttempt;
        });
    }

    /**
     * Get level multiplier based on quiz level
     *
     * @param int|null $levelId
     * @return float
     */
    protected function getLevelMultiplier(?int $levelId): float
    {
        return self::POINTS_CONFIG['level_multipliers'][$levelId] ?? 1.0;
    }

    /**
     * Calculate performance bonus based on success rate
     *
     * @param int $correctAnswers
     * @param int $totalQuestions
     * @return int
     */
    protected function calculatePerformanceBonus(int $correctAnswers, int $totalQuestions): int
    {
        $successRate = ($correctAnswers / $totalQuestions) * 100;

        foreach (self::POINTS_CONFIG['bonus_thresholds'] as $threshold => $bonus) {
            if ($successRate >= $threshold) {
                return $bonus;
            }
        }

        return 0;
    }

    /**
     * Calculate speed bonus if quiz was completed quickly
     *
     * @param Quiz $quiz
     * @param int|null $timeSpent
     * @return int
     */
    protected function calculateSpeedBonus(Quiz $quiz, ?int $timeSpent): int
    {
        if (!self::POINTS_CONFIG['time_bonus']['enabled'] ||
            !$timeSpent ||
            !$quiz->duration) {
            return 0;
        }

        $thresholdTime = $quiz->duration * 60 * (self::POINTS_CONFIG['time_bonus']['threshold_percent'] / 100);

        if ($timeSpent <= $thresholdTime) {
            $speedRatio = $timeSpent / $thresholdTime;
            $bonus = (1 - $speedRatio) * self::POINTS_CONFIG['time_bonus']['max_bonus'];

            return (int) $bonus;
        }

        return 0;
    }

    /**
     * Get user's total points across all quizzes
     *
     * @param User $user
     * @return int
     */
    public function getUserTotalPoints(User $user): int
    {
        return Score::where('user_id', $user->id)->sum('score');
    }

    /**
     * Get user's points for a specific quiz category
     *
     * @param User $user
     * @param int $categoryId
     * @return int
     */
    public function getUserCategoryPoints(User $user, int $categoryId): int
    {
        return Score::where('user_id', $user->id)
            ->whereHas('quiz', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->sum('score');
    }

    /**
     * Get points leaderboard
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getPointsLeaderboard(int $limit = 10): \Illuminate\Support\Collection
    {
        return User::select([
                'users.id',
                'users.username',
                'users.firstname',
                'users.lastname',
                DB::raw('COALESCE(SUM(scores.score), 0) as total_points'),
                DB::raw('COUNT(quiz_attempts.id) as quiz_attempts_count')
            ])
            ->leftJoin('scores', 'users.id', '=', 'scores.user_id')
            ->leftJoin('quiz_attempts', 'users.id', '=', 'quiz_attempts.user_id')
            ->groupBy('users.id', 'users.username', 'users.firstname', 'users.lastname')
            ->orderBy('total_points', 'desc')
            ->limit($limit)
            ->get();
    }
}
