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
        Log::info('Starting tasks:check-due-dates command');

        $maxDays = collect(NotificationSetting::select('notification_timing')
            ->get()
            ->pluck('notification_timing'))
            ->flatten()
            ->max();

        Task::with(['user.notificationSetting'])
            ->whereNotNull('due_date')
            ->where('status', '!=', 'completed')
            ->where('is_archived', false)
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays($maxDays))
            ->chunk(100, function ($tasks) {
                foreach ($tasks as $task) {
                    $this->processTask($task);
                }
            });

        Log::info('Completed tasks:check-due-dates command');
    }

    private function processTask($task)
    {
        try {
            $dueDate = Carbon::parse($task->due_date)->startOfDay();
            $now = Carbon::now()->startOfDay();
            $daysUntilDue = $now->diffInDays($dueDate, false);

            $notificationTiming = $task->user->notificationSetting->notification_timing ?? [14];

            Log::info('Processing task details', [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'due_date' => $task->due_date,
                'days_until_due' => $daysUntilDue,
                'notification_timing' => $notificationTiming,
                'exact_days' => $daysUntilDue,
                'timing_check' => in_array($daysUntilDue, $notificationTiming)
            ]);

            if (in_array($daysUntilDue, $notificationTiming)) {
                // 同じ日の通知がないか確認
                $existingNotification = Notification::where('task_id', $task->id)
                    ->where('type', 'task_due')
                    ->whereDate('created_at', $now)
                    ->exists();

                Log::info('Notification check', [
                    'task_id' => $task->id,
                    'existing_notification' => $existingNotification
                ]);

                if (!$existingNotification) {
                    // 通知を非同期的に作成
                    SendTaskDueNotification::dispatch($task, $daysUntilDue);

                    Log::info('Notification created', [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'days_until_due' => $daysUntilDue
                    ]);

                    $this->info("Notification created for task {$task->id}: {$task->title}");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in processTask', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
