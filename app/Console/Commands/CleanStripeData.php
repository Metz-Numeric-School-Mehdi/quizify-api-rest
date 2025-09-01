<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CleanStripeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:clean-data {--user-id= : Clean data for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean obsolete Stripe data when switching environments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
            $this->cleanUserStripeData($user);
        } else {
            $this->info('Cleaning Stripe data for all users...');
            User::whereNotNull('stripe_id')->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $this->cleanUserStripeData($user);
                }
            });
        }

        $this->info('Stripe data cleaning completed!');
        return 0;
    }

    private function cleanUserStripeData(User $user)
    {
        $this->info("Cleaning Stripe data for user: {$user->email} (ID: {$user->id})");

        // Reset Stripe fields
        $user->update([
            'stripe_id' => null,
            'pm_type' => null,
            'pm_last_four' => null,
            'trial_ends_at' => null,
        ]);

        // Cancel any active Stripe subscriptions in the database
        if ($user->subscriptions()->exists()) {
            $user->subscriptions()->update([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ]);
        }

        $this->line("  ✓ Cleaned Stripe data for {$user->email}");
    }
}
