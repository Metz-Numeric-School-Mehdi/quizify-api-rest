<?php

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

include base_path("routes/auth.php");

// Quizzes endpoints
Route::get("/quizzes", [QuizController::class, "index"]);
Route::post("/quizzes", [QuizController::class, "store"])->middleware("auth:sanctum");
Route::get("/quizzes/{id}", [QuizController::class, "show"]);
Route::delete("/quizzes/{id}", [QuizController::class, "destroy"])->middleware("auth:sanctum");
Route::put("/quizzes/{id}", [QuizController::class, "update"])->middleware("auth:sanctum");

// Questions endpoints
Route::get("/questions", [QuestionController::class, "index"]);
Route::post("/questions", [QuestionController::class, "store"]);
Route::get("/questions/{id}", [QuestionController::class, "show"]);
Route::delete("/questions/{id}", [QuestionController::class, "destroy"])->middleware(
    "auth:sanctum"
);
Route::put("/questions/{id}", [QuestionController::class, "update"])->middleware("auth:sanctum");

Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");
