<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Badge;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
}
