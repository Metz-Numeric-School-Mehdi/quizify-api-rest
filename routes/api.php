<?php

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

include base_path("routes/auth.php");

Route::get("/quizzes", [QuizController::class, "index"]);
Route::post("/quizzes", [QuizController::class, "store"])->middleware("auth:sanctum");
Route::get("/quizzes/{id}", [QuizController::class, "show"]);
Route::post("/quizzes/{quiz}/submit", [QuizController::class, "submit"])->middleware("auth:sanctum");
Route::post("/quizzes/{quiz}/attempt", [QuizController::class, "storeAttempt"])->middleware("auth:sanctum");
Route::delete("/quizzes/{id}", [QuizController::class, "destroy"])->middleware("auth:sanctum");
Route::put("/quizzes/{id}", [QuizController::class, "update"])->middleware("auth:sanctum");

Route::get("/questions", [QuestionController::class, "index"]);
Route::post("/questions", [QuestionController::class, "store"]);
Route::get("/questions/{id}", [QuestionController::class, "show"]);
Route::delete("/questions/{id}", [QuestionController::class, "destroy"])->middleware("auth:sanctum");
Route::put("/questions/{id}", [QuestionController::class, "update"])->middleware("auth:sanctum");

Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");

Route::post("/users/{user}/assign-badges", [UserController::class, "assignBadges"])->middleware("auth:sanctum");
Route::get("/leaderboard", [UserController::class, "leaderboard"]);

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
Route::delete('/answers/{id}', [App\Http\Controllers\AnswerController::class, 'destroy'])->middleware('auth:sanctum');;

Route::get('/question-responses', [App\Http\Controllers\QuestionResponseController::class, 'index']);
Route::post('/question-responses', [App\Http\Controllers\QuestionResponseController::class, 'store'])->middleware('auth:sanctum');
Route::get('/question-responses/{id}', [App\Http\Controllers\QuestionResponseController::class, 'show']);
Route::put('/question-responses/{id}', [App\Http\Controllers\QuestionResponseController::class, 'update']);
Route::delete('/question-responses/{id}', [App\Http\Controllers\QuestionResponseController::class, 'destroy']);
Route::get('/quiz-levels', [App\Http\Controllers\QuizLevelController::class, 'index']);
Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index']);
