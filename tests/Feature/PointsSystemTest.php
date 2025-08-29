<?php

use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizLevel;
use App\Services\PointsCalculationService;

test('points calculation service calculates correctly', function () {
    $pointsService = new PointsCalculationService();

    $quiz = new Quiz([
        'level_id' => 2,
        'duration' => 30
    ]);
    $quiz->level = new QuizLevel(['id' => 2]);

    $correctAnswers = 5;
    $totalQuestions = 5;
    $timeSpent = 600;

    $pointsData = $pointsService->calculatePoints(
        $quiz,
        $correctAnswers,
        $totalQuestions,
        $timeSpent
    );

    expect($pointsData['base_points'])->toBe(50);
    expect($pointsData['level_multiplier'])->toBe(1.5);
    expect($pointsData['level_points'])->toBe(75);
    expect($pointsData['performance_bonus'])->toBe(50);
    expect($pointsData['total_points'])->toBeGreaterThan(125);
});

test('points calculation for average score', function () {
    $pointsService = new PointsCalculationService();

    $quiz = new Quiz([
        'level_id' => 2,
        'duration' => 30
    ]);
    $quiz->level = new QuizLevel(['id' => 2]);

    $correctAnswers = 3;
    $totalQuestions = 5;
    $timeSpent = 1200;

    $pointsData = $pointsService->calculatePoints(
        $quiz,
        $correctAnswers,
        $totalQuestions,
        $timeSpent
    );

    expect($pointsData['base_points'])->toBe(30);
    expect($pointsData['level_points'])->toBe(45);
    expect($pointsData['performance_bonus'])->toBe(0);
    expect($pointsData['total_points'])->toBe(45);
});

test('no speed bonus when score is zero', function () {
    $pointsService = new PointsCalculationService();

    $quiz = new Quiz([
        'level_id' => 2,
        'duration' => 30
    ]);
    $quiz->level = new QuizLevel(['id' => 2]);

    $correctAnswers = 0;
    $totalQuestions = 5;
    $timeSpent = 300;

    $pointsData = $pointsService->calculatePoints(
        $quiz,
        $correctAnswers,
        $totalQuestions,
        $timeSpent
    );

    expect($pointsData['base_points'])->toBe(0);
    expect($pointsData['level_points'])->toBe(0);
    expect($pointsData['performance_bonus'])->toBe(0);
    expect($pointsData['speed_bonus'])->toBe(0);
    expect($pointsData['total_points'])->toBe(0);
});
