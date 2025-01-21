<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Jobs\SendTaskDueNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckTaskDueDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-due-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check tasks due dates and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tasks = Task::with(['user.notificationSetting'])
            ->whereNotNull('due_date')
            ->where('status', '!=', 'completed')
            ->where('is_archived', false)
            ->get();

        foreach ($tasks as $task) {
            $dueDate = Carbon::parse($task->due_date);
            $daysUntilDue = Carbon::now()->startOfDay()->diffInDays($dueDate, false);

            if ($daysUntilDue < 0) {
                continue;
            }

            $notificationTiming = $task->user->notificationSetting->notification_timing ?? [1, 3, 7];

            if (in_array($daysUntilDue, $notificationTiming)) {
                SendTaskDueNotification::dispatch($task, $daysUntilDue);
            }
        }
    }
}
