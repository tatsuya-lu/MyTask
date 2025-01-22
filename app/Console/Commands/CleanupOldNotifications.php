<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;

class CleanupOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up notifications older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thirtyDaysAgo = now()->subDays(30);

        // チャンク処理で少しずつ削除
        Notification::where('created_at', '<', $thirtyDaysAgo)
            ->chunkById(1000, function ($notifications) {
                foreach ($notifications as $notification) {
                    $notification->delete();
                }
            });

        $this->info('Old notifications cleaned up successfully.');
    }
}
