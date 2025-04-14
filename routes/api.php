<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

include base_path("routes/auth.php");

Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");
