<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskService
{
    public function createTask(array $data)
    {
        $data['user_id'] = Auth::id();

        if (isset($data['due_date'])) {
            $data['due_date'] = Carbon::parse($data['due_date'])
                ->startOfDay()
                ->format('Y-m-d');
        }

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $task = Task::create($data);

        if (!empty($tags)) {
            $task->tags()->sync($tags);
        }

        return $task->load(['tags']);
    }

    public function updateTask(Task $task, array $data)
    {
        if (isset($data['due_date'])) {
            $data['due_date'] = Carbon::parse($data['due_date'])
                ->startOfDay()
                ->format('Y-m-d');
        }

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $task->update($data);

        if (isset($tags)) {
            $task->tags()->sync($tags);
        }

        return $task->load(['tags']);
    }

    public function getFilteredTasks(array $filters = [])
    {
        $query = Task::with(['tags'])
            ->where('user_id', auth()->id());

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();

            $query->whereBetween('due_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);
        } elseif (!empty($filters['year'])) {
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

        if (isset($filters['is_archived'])) {
            $query->where('is_archived', $filters['is_archived']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            return $query->orderBy('due_date', 'asc');
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function deleteTask(Task $task)
    {
        return $task->delete();
    }
}
