<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Jobs\SendTaskDueNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\NotificationSetting;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

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
        $maxDays = collect(NotificationSetting::select('notification_timing')
            ->get()
            ->pluck('notification_timing'))
            ->flatten()
            ->max();

        Task::with(['user.notificationSetting'])
            ->whereNotNull('due_date')
            ->where('status', '!=', 'completed')
            ->where('is_archived', false)
            ->where('due_date', '<=', now()->addDays($maxDays))
            ->chunk(100, function ($tasks) {
                foreach ($tasks as $task) {
                    $this->processTask($task);
                }
            });
    }

    private function processTask($task)
{
    $dueDate = Carbon::parse($task->due_date);
    $now = Carbon::now()->startOfDay();
    $daysUntilDue = $now->diffInDays($dueDate, false);

    \Log::info('Processing task', [
        'task_id' => $task->id,
        'due_date' => $task->due_date,
        'days_until_due' => $daysUntilDue,
        'notification_timing' => $task->user->notificationSetting->notification_timing ?? [1, 3, 7]
    ]);

    // 期限日を含めて通知を処理
    if ($daysUntilDue >= 0) {
        $notificationTiming = $task->user->notificationSetting->notification_timing ?? [1, 3, 7];

        // 当日の通知も含める
        if (in_array($daysUntilDue, array_merge([0], $notificationTiming))) {
            $existingNotification = Notification::where('task_id', $task->id)
                ->where('type', 'task_due')
                ->whereDate('created_at', $now->toDateString())
                ->exists();

            if (!$existingNotification) {
                SendTaskDueNotification::dispatch($task, $daysUntilDue);
                $this->info("Notification dispatched for task {$task->id} ({$daysUntilDue} days until due)");
            }
        }
    }
}
}
