<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    /**
     * Handle an incoming request to check subscription limits
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limitType): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $plan = $user->subscriptionPlan;

        if (!$plan) {
            return response()->json(['message' => 'Aucun plan d\'abonnement trouvé'], 403);
        }

        switch ($limitType) {
            case 'quiz_creation':
                return $this->checkQuizCreationLimit($user, $plan, $next, $request);

            case 'question_creation':
                return $this->checkQuestionCreationLimit($user, $plan, $next, $request);

            case 'quiz_participation':
                return $this->checkQuizParticipationLimit($user, $plan, $next, $request);

            case 'analytics_access':
                return $this->checkAnalyticsAccess($user, $plan, $next, $request);

            case 'export_access':
                return $this->checkExportAccess($user, $plan, $next, $request);

            case 'team_management':
                return $this->checkTeamManagementAccess($user, $plan, $next, $request);

            default:
                return $next($request);
        }
    }

    /**
     * Check quiz creation limits
     */
    private function checkQuizCreationLimit($user, $plan, $next, $request): Response
    {
        if ($plan->max_quizzes === null) {
            return $next($request);
        }

        $userQuizCount = $user->quizzesCreated()->count();

        if ($userQuizCount >= $plan->max_quizzes) {
            return response()->json([
                'message' => 'Limite de création de quiz atteinte pour votre plan',
                'current_count' => $userQuizCount,
                'max_allowed' => $plan->max_quizzes,
                'plan' => $plan->name,
                'upgrade_required' => true
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check question creation limits per quiz
     */
    private function checkQuestionCreationLimit($user, $plan, $next, $request): Response
    {
        if ($plan->max_questions_per_quiz === null) {
            return $next($request);
        }

        $quizId = $request->route('quiz_id') ?? $request->input('quiz_id');

        if ($quizId) {
            $questionCount = \App\Models\Question::where('quiz_id', $quizId)->count();

            if ($questionCount >= $plan->max_questions_per_quiz) {
                return response()->json([
                    'message' => 'Limite de questions par quiz atteinte pour votre plan',
                    'current_count' => $questionCount,
                    'max_allowed' => $plan->max_questions_per_quiz,
                    'plan' => $plan->name,
                    'upgrade_required' => true
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Check quiz participation limits
     */
    private function checkQuizParticipationLimit($user, $plan, $next, $request): Response
    {
        if ($plan->max_participants === null) {
            return $next($request);
        }

        $quizId = $request->route('quizId') ?? $request->route('quiz');

        if ($quizId) {
            $participantCount = \App\Models\QuizAttempt::where('quiz_id', $quizId)
                ->distinct('user_id')
                ->count('user_id');

            if ($participantCount >= $plan->max_participants) {
                return response()->json([
                    'message' => 'Limite de participants atteinte pour ce quiz',
                    'current_count' => $participantCount,
                    'max_allowed' => $plan->max_participants,
                    'plan' => $plan->name,
                    'upgrade_required' => true
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Check analytics access
     */
    private function checkAnalyticsAccess($user, $plan, $next, $request): Response
    {
        if (!$plan->analytics_enabled) {
            return response()->json([
                'message' => 'Accès aux analyses non disponible avec votre plan actuel',
                'plan' => $plan->name,
                'feature_required' => 'analytics_enabled',
                'upgrade_required' => true
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check export access
     */
    private function checkExportAccess($user, $plan, $next, $request): Response
    {
        if (!$plan->export_enabled) {
            return response()->json([
                'message' => 'Fonctionnalité d\'export non disponible avec votre plan actuel',
                'plan' => $plan->name,
                'feature_required' => 'export_enabled',
                'upgrade_required' => true
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check team management access
     */
    private function checkTeamManagementAccess($user, $plan, $next, $request): Response
    {
        if (!$plan->team_management) {
            return response()->json([
                'message' => 'Gestion d\'équipe non disponible avec votre plan actuel',
                'plan' => $plan->name,
                'feature_required' => 'team_management',
                'upgrade_required' => true
            ], 403);
        }

        return $next($request);
    }
}
