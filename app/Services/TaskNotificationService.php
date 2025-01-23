<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use Carbon\Carbon;

class TaskNotificationService
{
    public function cancelDueNotifications(Task $task)
    {
        Notification::where('task_id', $task->id)
            ->where('type', 'task_due')
            ->whereDate('created_at', '>=', now()->toDateString())
            ->delete();
    }
}