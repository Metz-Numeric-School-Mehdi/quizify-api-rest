<?php

// Authentication endpoints

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->group(function () {
    Route::post("/signin", [AuthController::class, "signIn"]);
    Route::post("/signup", [AuthController::class, "signUp"]);
    Route::get("/signout", [AuthController::class, "signOut"])->middleware("auth:sanctum");
});
