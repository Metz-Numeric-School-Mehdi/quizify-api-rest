<?php

use App\Http\Controllers\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

include base_path("routes/auth.php");

Route::get("/quizzes", [QuizController::class, "index"]);
Route::post("/quizzes", [QuizController::class, "store"])->middleware("auth:sanctum");
Route::delete("/quizzes/{id}", [QuizController::class, "destroy"])->middleware("auth:sanctum");
Route::put("/quizzes/{id}", [QuizController::class, "update"])->middleware("auth:sanctum");

Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");
