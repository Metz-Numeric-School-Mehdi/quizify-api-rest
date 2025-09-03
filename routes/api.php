<?php

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\OrderingQuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

include base_path("routes/auth.php");

Route::get('/quizzes/search', [QuizController::class, 'search']);
Route::get('/quizzes/{quizId}', [QuizController::class, 'show']);

Route::apiResource('quizzes', QuizController::class)->middleware(['auth:sanctum']);
Route::apiResource('questions', QuestionController::class)->middleware(['auth:sanctum']);

Route::post("quizzes/{quizId}/submit", [QuizController::class, 'submit'])->middleware(['auth:sanctum']);

Route::get("/quizzes/{quizId}/questions", [QuestionController::class, "getByQuiz"]);

Route::get("/user", function (Request $request) {
    return $request->user()->load('subscriptionPlan');
})->middleware("auth:sanctum");

Route::post("/users/{user}/assign-badges", [UserController::class, "assignBadges"])->middleware("auth:sanctum");

Route::post('/ordering-questions', [OrderingQuestionController::class, 'createOrderingQuestion'])->middleware('auth:sanctum');
Route::get('/ordering-questions/{questionId}', [OrderingQuestionController::class, 'getOrderingQuestion']);
Route::post('/ordering-questions/{questionId}/submit', [OrderingQuestionController::class, 'submitOrderingResponse'])->middleware('auth:sanctum');
Route::get('/quizzes/{quizId}/ordering-questions', [OrderingQuestionController::class, 'getOrderingQuestionsByQuiz']);
Route::put('/ordering-questions/{questionId}', [OrderingQuestionController::class, 'updateOrderingQuestion'])->middleware('auth:sanctum');

Route::get("/leaderboard", [LeaderboardController::class, "index"]);
Route::get("/leaderboard/category/{categoryId}", [LeaderboardController::class, "byCategory"]);
Route::get("/leaderboard/organization/{organizationId}", [LeaderboardController::class, "byOrganization"]);
Route::post("/leaderboard/update-rankings", [LeaderboardController::class, "updateRankings"])->middleware("auth:sanctum");

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/points/user', [App\Http\Controllers\PointsController::class, 'getUserPoints']);
    Route::get('/points/user/category/{categoryId}', [App\Http\Controllers\PointsController::class, 'getUserCategoryPoints']);
});
Route::get('/points/leaderboard', [App\Http\Controllers\PointsController::class, 'getLeaderboard']);
Route::get('/points/config', [App\Http\Controllers\PointsController::class, 'getPointsConfig']);

Route::get('/organizations', [App\Http\Controllers\OrganizationController::class, 'index']);
Route::post('/organizations', [App\Http\Controllers\OrganizationController::class, 'store'])->middleware('auth:sanctum');
Route::get('/organizations/{id}', [App\Http\Controllers\OrganizationController::class, 'show']);
Route::put('/organizations/{id}', [App\Http\Controllers\OrganizationController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/organizations/{id}', [App\Http\Controllers\OrganizationController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/teams', [App\Http\Controllers\TeamController::class, 'index']);
Route::post('/teams', [App\Http\Controllers\TeamController::class, 'store'])->middleware('auth:sanctum');
Route::get('/teams/{id}', [App\Http\Controllers\TeamController::class, 'show']);
Route::put('/teams/{id}', [App\Http\Controllers\TeamController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/teams/{id}', [App\Http\Controllers\TeamController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/badges', [App\Http\Controllers\BadgeController::class, 'index']);
Route::post('/badges', [App\Http\Controllers\BadgeController::class, 'store'])->middleware('auth:sanctum');
Route::get('/badges/{id}', [App\Http\Controllers\BadgeController::class, 'show']);
Route::put('/badges/{id}', [App\Http\Controllers\BadgeController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/badges/{id}', [App\Http\Controllers\BadgeController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/scores', [App\Http\Controllers\ScoreController::class, 'index']);
Route::post('/scores', [App\Http\Controllers\ScoreController::class, 'store'])->middleware('auth:sanctum');
Route::get('/scores/{id}', [App\Http\Controllers\ScoreController::class, 'show']);
Route::put('/scores/{id}', [App\Http\Controllers\ScoreController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/scores/{id}', [App\Http\Controllers\ScoreController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/question-types', [App\Http\Controllers\QuestionTypeController::class, 'index']);
Route::post('/question-types', [App\Http\Controllers\QuestionTypeController::class, 'store'])->middleware('auth:sanctum');
Route::get('/question-types/{id}', [App\Http\Controllers\QuestionTypeController::class, 'show']);
Route::put('/question-types/{id}', [App\Http\Controllers\QuestionTypeController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/question-types/{id}', [App\Http\Controllers\QuestionTypeController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/answers', [App\Http\Controllers\AnswerController::class, 'index']);
Route::post('/answers', [App\Http\Controllers\AnswerController::class, 'store'])->middleware('auth:sanctum');
Route::get('/answers/{id}', [App\Http\Controllers\AnswerController::class, 'show']);
Route::put('/answers/{id}', [App\Http\Controllers\AnswerController::class, 'update'])->middleware("auth:sanctum");
Route::delete('/answers/{id}', [App\Http\Controllers\AnswerController::class, 'destroy'])->middleware('auth:sanctum');
;

Route::get('/question-responses', [App\Http\Controllers\QuestionResponseController::class, 'index']);
Route::post('/question-responses', [App\Http\Controllers\QuestionResponseController::class, 'store'])->middleware('auth:sanctum');
Route::get('/question-responses/{id}', [App\Http\Controllers\QuestionResponseController::class, 'show']);
Route::put('/question-responses/{id}', [App\Http\Controllers\QuestionResponseController::class, 'update']);
Route::delete('/question-responses/{id}', [App\Http\Controllers\QuestionResponseController::class, 'destroy']);
Route::get('/quiz-levels', [App\Http\Controllers\QuizLevelController::class, 'index']);
Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index']);

Route::get('/subscription/plans', [App\Http\Controllers\SubscriptionController::class, 'plans']);

Route::get('/subscription/success', [App\Http\Controllers\SubscriptionController::class, 'success']);
Route::get('/subscription/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancelled']);

Route::get('/checkout', [App\Http\Controllers\SubscriptionController::class, 'checkout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/subscription/create', [App\Http\Controllers\SubscriptionController::class, 'createSubscription']);
    Route::post('/subscription/confirm-payment', [App\Http\Controllers\SubscriptionController::class, 'confirmPayment']);
    Route::get('/subscription/current', [App\Http\Controllers\SubscriptionController::class, 'currentSubscription']);
    Route::post('/subscription/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancelSubscription']);
    Route::post('/subscription/swap', [App\Http\Controllers\SubscriptionController::class, 'swapSubscription']);
    Route::post('/subscription/sync', [App\Http\Controllers\SubscriptionController::class, 'syncSubscription']);
    Route::get('/subscription/billing-portal', [App\Http\Controllers\SubscriptionController::class, 'billingPortal']);
});

Route::post('/webhook/stripe', [App\Http\Controllers\SubscriptionController::class, 'handleWebhook']);
