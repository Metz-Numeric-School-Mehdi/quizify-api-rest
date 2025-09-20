<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test route for security headers
Route::get('/test-security', function () {
    return response()->json(['message' => 'Test endpoint for security headers']);
});
