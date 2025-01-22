<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\Notification;
use App\Mail\TaskDueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendTaskDueNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;
    protected $daysUntilDue;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task, int $daysUntilDue)
    {
        $this->task = $task;
        $this->daysUntilDue = $daysUntilDue;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        \Log::info('Starting notification job', [
            'task_id' => $this->task->id,
            'days_until_due' => $this->daysUntilDue,
            'user_id' => $this->task->user_id
        ]);

        try {
            $user = $this->task->user;
            $settings = $user->notificationSetting;

            if (!$settings) {
                \Log::warning('No notification settings found for user', ['user_id' => $user->id]);
                return;
            }

            // トランザクション内での処理
            \DB::transaction(function () use ($user, $settings) {
                if ($settings->in_app_notifications_enabled) {
                    $notification = Notification::create([
                        'user_id' => $user->id,
                        'type' => 'task_due',
                        'task_id' => $this->task->id,
                        'title' => 'タスク期限通知',
                        'content' => $this->daysUntilDue === 0
                            ? "タスク「{$this->task->title}」の期限日です。"
                            : "タスク「{$this->task->title}」の期限まであと{$this->daysUntilDue}日です。",
                    ]);

                    \Log::info('Notification created', [
                        'notification_id' => $notification->id,
                        'task_id' => $this->task->id
                    ]);
                }

                if ($settings->email_notifications_enabled && $user->email) {
                    Mail::to($user->email)
                        ->send(new TaskDueNotification($this->task, $this->daysUntilDue));

                    \Log::info('Email notification sent', [
                        'user_email' => $user->email,
                        'task_id' => $this->task->id
                    ]);
                }
            });
        } catch (\Exception $e) {
            \Log::error('Error in SendTaskDueNotification job', [
                'error' => $e->getMessage(),
                'task_id' => $this->task->id
            ]);
            throw $e;
        }
    }
}
