<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteExpiredUnverifiedUsers extends Command
{
    protected $signature = 'users:delete-expired-unverified';

    protected $description = 'Delete unverified users older than 24 hours.';

    public function handle(): int
    {
        $deleted = 0;

        User::whereNull('email_verified_at')
            ->where('created_at', '<', now()->subDay())
            ->chunkById(100, function ($users) use (&$deleted) {
                foreach ($users as $user) {
                    $user->tokens()->delete();
                    $user->delete();
                    $deleted++;
                }
            });

        $this->info("Deleted {$deleted} expired unverified users.");

        return self::SUCCESS;
    }
}
