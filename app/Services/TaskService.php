<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    public function createTask(array $data)
    {
        $data['user_id'] = Auth::id();

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $task = Task::create($data);

        if (!empty($tags)) {
            $task->tags()->sync($tags);
        }

        return $task->load(['tags', 'team']);
    }

    public function updateTask(Task $task, array $data)
    {
        if (isset($data['due_date'])) {
            $data['due_date'] = \Carbon\Carbon::parse($data['due_date'])->format('Y-m-d');
        }

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $task->update($data);

        if (isset($tags)) {
            $task->tags()->sync($tags);
        }

        return $task->load(['tags', 'team']);
    }

    public function getFilteredTasks(array $filters = [])
    {
        $query = Task::with(['tags', 'team'])
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                    ->orWhereIn('team_id', auth()->user()->teams->pluck('id'));
            });

        // 年でフィルタリング
        if (!empty($filters['year'])) {
            $query->whereYear('due_date', $filters['year']);
        }

        if (!empty($filters['tag_ids'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tag_ids']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['team_id'])) {
            $query->where('team_id', $filters['team_id']);
        }

        if (isset($filters['is_archived'])) {
            $query->where('is_archived', $filters['is_archived']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function deleteTask(Task $task)
    {
        return $task->delete();
    }
}
