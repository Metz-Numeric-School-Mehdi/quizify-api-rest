<?php

use App\Services\LeaderboardService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');


// Planifie l'appel toutes les minutes
Schedule::call(function () {
    app(LeaderboardService::class)->updateAllRankings();
    Log::info('Leaderboard rankings updated successfully.');
})->everyMinute();
