<?php

namespace App\Services;

use App\Models\User;
use App\Models\SubscriptionPlan;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;

class SubscriptionService
{
    /**
     * Create a new subscription for a user
     *
     * @param User $user
     * @param SubscriptionPlan $plan
     * @param string $paymentMethod
     * @return array
     * @throws IncompletePayment|ApiErrorException
     */
    public function createSubscription(User $user, SubscriptionPlan $plan, string $paymentMethod): array
    {
        if ($plan->isFreePlan()) {
            return $this->assignFreePlan($user, $plan);
        }

        if ($user->subscribed('default')) {
            $user->subscription('default')->cancel();
        }

        $subscription = $user->newSubscription('default', $plan->stripe_price_id)
            ->create($paymentMethod);

        $user->subscription_plan_id = $plan->id;
        $user->save();

        return [
            'subscription' => $subscription,
            'plan' => $plan,
            'success' => true
        ];
    }

    /**
     * Assign free plan to user
     *
     * @param User $user
     * @param SubscriptionPlan $plan
     * @return array
     */
    public function assignFreePlan(User $user, SubscriptionPlan $plan): array
    {
        $user->subscription_plan_id = $plan->id;
        $user->save();

        return [
            'subscription' => null,
            'plan' => $plan,
            'success' => true
        ];
    }

    /**
     * Check if user can create more quizzes
     *
     * @param User $user
     * @return bool
     */
    public function canCreateQuiz(User $user): bool
    {
        $plan = $user->subscriptionPlan;

        if (!$plan || $plan->max_quizzes === null) {
            return true;
        }

        return $user->quizzesCreated()->count() < $plan->max_quizzes;
    }

    /**
     * Check if user can add more questions to a quiz
     *
     * @param User $user
     * @param int $quizId
     * @return bool
     */
    public function canAddQuestion(User $user, int $quizId): bool
    {
        $plan = $user->subscriptionPlan;

        if (!$plan || $plan->max_questions_per_quiz === null) {
            return true;
        }

        $questionCount = \App\Models\Question::where('quiz_id', $quizId)->count();
        return $questionCount < $plan->max_questions_per_quiz;
    }

    /**
     * Check if user can participate in quiz
     *
     * @param User $user
     * @param int $quizId
     * @return bool
     */
    public function canParticipateInQuiz(User $user, int $quizId): bool
    {
        $quiz = \App\Models\Quiz::find($quizId);
        if (!$quiz) {
            return false;
        }

        $creatorPlan = $quiz->user->subscriptionPlan;

        if (!$creatorPlan || $creatorPlan->max_participants === null) {
            return true;
        }

        $participantCount = \App\Models\QuizAttempt::where('quiz_id', $quizId)
            ->distinct('user_id')
            ->count('user_id');

        return $participantCount < $creatorPlan->max_participants;
    }

    /**
     * Check if user has access to analytics
     *
     * @param User $user
     * @return bool
     */
    public function hasAnalyticsAccess(User $user): bool
    {
        $plan = $user->subscriptionPlan;
        return $plan && $plan->analytics_enabled;
    }

    /**
     * Check if user has export access
     *
     * @param User $user
     * @return bool
     */
    public function hasExportAccess(User $user): bool
    {
        $plan = $user->subscriptionPlan;
        return $plan && $plan->export_enabled;
    }

    /**
     * Check if user has team management access
     *
     * @param User $user
     * @return bool
     */
    public function hasTeamManagementAccess(User $user): bool
    {
        $plan = $user->subscriptionPlan;
        return $plan && $plan->team_management;
    }

    /**
     * Check if user has priority support
     *
     * @param User $user
     * @return bool
     */
    public function hasPrioritySupport(User $user): bool
    {
        $plan = $user->subscriptionPlan;
        return $plan && $plan->priority_support;
    }

    /**
     * Get usage statistics for a user
     *
     * @param User $user
     * @return array
     */
    public function getUserUsageStats(User $user): array
    {
        $plan = $user->subscriptionPlan;

        $stats = [
            'plan' => $plan ? $plan->name : 'Aucun plan',
            'quizzes_created' => $user->quizzesCreated()->count(),
            'max_quizzes' => $plan ? $plan->max_quizzes : 'Illimité',
            'subscription_active' => $user->subscribed('default'),
        ];

        if ($plan) {
            $stats['features'] = [
                'analytics_enabled' => $plan->analytics_enabled,
                'export_enabled' => $plan->export_enabled,
                'team_management' => $plan->team_management,
                'priority_support' => $plan->priority_support,
            ];
        }

        return $stats;
    }

    /**
     * Get recommended upgrade plan for user
     *
     * @param User $user
     * @return SubscriptionPlan|null
     */
    public function getRecommendedUpgrade(User $user): ?SubscriptionPlan
    {
        $currentPlan = $user->subscriptionPlan;

        if (!$currentPlan) {
            return SubscriptionPlan::where('slug', 'free')->first();
        }

        if ($currentPlan->slug === 'free') {
            return SubscriptionPlan::where('slug', 'premium')->first();
        }

        if ($currentPlan->slug === 'premium') {
            return SubscriptionPlan::where('slug', 'business')->first();
        }

        return null;
    }

    /**
     * Cancel subscription and downgrade to free plan
     *
     * @param User $user
     * @return bool
     */
    public function cancelAndDowngrade(User $user): bool
    {
        $subscription = $user->subscription('default');

        if ($subscription) {
            $subscription->cancel();
        }

        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        if ($freePlan) {
            $user->subscription_plan_id = $freePlan->id;
            $user->save();
            return true;
        }

        return false;
    }
}
