<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Badge;
use App\Http\Resources\UserResource;
use App\Repositories\User\UserRepository;
use App\Http\Modules\Users\Strategies\UserRuleStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile with statistics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        try {
            $user = User::with('subscriptionPlan')
                ->find($request->user()->id);

            if (!$user) {
                return response()->json([
                    "message" => "Utilisateur non trouvé."
                ], 404);
            }

            return response()->json([
                'user' => new UserResource($user)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Erreur lors de la récupération du profil.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Classement des utilisateurs par ranking croissant (leaderboard).
     */
    public function leaderboard()
    {
        $users = \App\Models\User::orderBy('ranking')
            ->take(20)
            ->get(['id', 'username', 'ranking', 'profile_photo']);
        return response()->json($users);
    }

    public function assignBadges(Request $request, $userId)
    {
        try {
            $user = User::with("quizzes", "badges")->findOrFail($userId);
            $badgesToAssign = [];

            if ($user->quizzes()->wherePivot("score", ">=", 70)->count() >= 1) {
                $badge = Badge::where("name", "Débutant")->first();
                if ($badge && !$user->badges->contains($badge->id)) {
                    $user->badges()->attach($badge->id);
                    $badgesToAssign[] = $badge;
                }
            }

            if ($user->quizzes()->count() >= 5) {
                $badge = Badge::where("name", "Assidu")->first();
                if ($badge && !$user->badges->contains($badge->id)) {
                    $user->badges()->attach($badge->id);
                    $badgesToAssign[] = $badge;
                }
            }

            return response()->json([
                "badges_awarded" => $badgesToAssign,
                "all_user_badges" => $user->badges,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString(),
                ],
                500
            );
        }
    }

    /**
     * Update the authenticated user's profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            $userRepository = new UserRepository();
            $ruleStrategy = new UserRuleStrategy();

            // Validate request data
            $validator = Validator::make(
                $request->all(),
                $ruleStrategy->getProfileUpdateRules($user->id),
                $ruleStrategy->getMessages()
            );

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation des données.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validatedData = $validator->validated();

            // Update user profile using repository
            $updatedUser = $userRepository->updateProfile($validatedData, $user->id);

            // Generate profile photo URL if exists
            $profilePhotoUrl = $userRepository->getProfilePhotoUrl($updatedUser);

            return response()->json([
                'message' => 'Profil mis à jour avec succès.',
                'user' => $updatedUser,
                'profile_photo_url' => $profilePhotoUrl,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du profil.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
