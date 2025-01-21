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
        $user = $this->task->user;
        $settings = $user->notificationSetting;

        if (!$settings) {
            return;
        }

        // アプリ内通知を作成
        if ($settings->in_app_notifications_enabled) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'task_due',
                'task_id' => $this->task->id,
                'title' => 'タスク期限通知',
                'content' => "タスク「{$this->task->title}」の期限まであと{$this->daysUntilDue}日です。",
            ]);
        }

        // メール通知を送信
        if ($settings->email_notifications_enabled) {
            Mail::to($user->email)
                ->send(new TaskDueNotification($this->task, $this->daysUntilDue));
        }
    }
}
