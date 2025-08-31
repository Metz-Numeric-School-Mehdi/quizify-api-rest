<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-security', function () {
    return response()->json(['message' => 'Test endpoint for security headers']);
});
