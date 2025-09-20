<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeaderboardService;

class UpdateLeaderboardRanking extends Command
{
    protected $signature = 'leaderboard:update-ranking';
    protected $description = 'Update user rankings for the leaderboard';

    public function handle()
    {
        $service = app(LeaderboardService::class);
        $service->updateAllRankings();
        $this->info('Leaderboard rankings updated!');
    }
}