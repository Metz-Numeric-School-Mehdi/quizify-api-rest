<?php

namespace App\Http\Controllers;

use App\Models\QuizLevel;

class QuizLevelController extends Controller
{
    public function index()
    {
        return QuizLevel::all();
    }
}
