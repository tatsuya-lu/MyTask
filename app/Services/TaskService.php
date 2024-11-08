<?php

namespace App\Service;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    public function createTask(array $data)
    {
        $data['user_id'] = Auth::id();
        $task = Task::create($data);

        if (isset($data['tags'])) {
            $task->tags()->sync($data['tags']);
        }

        return $task;
    }

    public function updateTask(Task $task, array $data)
    {
        $task->update($data);

        if (isset($data['tags'])) {
            $task->tags()->sync($data['tags']);
        }

        return $task;
    }

    public function deleteTask(Task $task)
    {
        return $task->delete();
    }
}
