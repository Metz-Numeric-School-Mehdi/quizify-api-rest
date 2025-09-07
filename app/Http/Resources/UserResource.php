<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array for user profile information.
     * Excludes sensitive fields and includes user statistics.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'avatar' => $this->avatar,
            'profile_photo' => $this->profile_photo,
            'ranking' => $this->ranking,
            'role_id' => $this->role_id,
            'organization_id' => $this->organization_id,
            'team_id' => $this->team_id,
            'subscription' => [
                'subscription_plan_id' => $this->subscription_plan_id,
                'subscription_plan' => $this->subscriptionPlan,
                'stripe_id' => $this->stripe_id,
                'trial_ends_at' => $this->trial_ends_at,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            'statistics' => [
                'quizzes_created_count' => $this->quizzesCreated()->count(),
                'quizzes_played_count' => $this->quizAttempts()->distinct('quiz_id')->count(),
                'total_points' => $this->getTotalPoints(),
            ],
        ];
    }
}
