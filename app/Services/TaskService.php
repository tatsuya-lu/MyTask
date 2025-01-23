<?php

namespace App\Services;

use App\Models\Task;
use App\Models\UserTaskOrder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $userOrder = UserTaskOrder::where('user_id', auth()->id())
            ->where('is_custom_order', true)
            ->first();

        if ($userOrder && !empty($userOrder->task_order) && empty($filters['start_date']) && empty($filters['end_date'])) {
            $cases = [];
            foreach ($userOrder->task_order as $index => $id) {
                $cases[] = "WHEN id = {$id} THEN {$index}";
            }

            if (!empty($cases)) {
                $orderByCase = "CASE " . implode(' ', $cases) . " ELSE " . count($userOrder->task_order) . " END";
                return $query->orderByRaw($orderByCase);
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function updateTaskOrder(array $taskOrder, bool $isCustomOrder)
    {
        $userId = auth()->id();

        return DB::transaction(function () use ($userId, $taskOrder, $isCustomOrder) {
            $orderSetting = UserTaskOrder::updateOrCreate(
                ['user_id' => $userId],
                [
                    'task_order' => $taskOrder,
                    'is_custom_order' => $isCustomOrder
                ]
            );

            foreach ($taskOrder as $index => $taskId) {
                Task::where('id', $taskId)
                    ->where('user_id', $userId)
                    ->update(['sort_order' => $index]);
            }

            return $orderSetting;
        });
    }

    public function deleteTask(Task $task)
    {
        return $task->delete();
    }

    public function saveTaskOrder(array $taskOrder, bool $isCustomOrder, ?string $name = null, ?string $description = null)
    {
        $userId = auth()->id();

        if (UserTaskOrder::hasReachedLimit($userId)) {
            throw new \Exception('保存できる並び順は10個までです。');
        }

        return UserTaskOrder::create([
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'task_order' => $taskOrder,
            'is_custom_order' => $isCustomOrder
        ]);
    }

    public function getSavedTaskOrders()
    {
        return UserTaskOrder::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateSavedTaskOrder($orderId, array $data)
    {
        $order = UserTaskOrder::where('user_id', auth()->id())
            ->findOrFail($orderId);

        return $order->update($data);
    }

    public function deleteSavedTaskOrder($orderId)
    {
        return UserTaskOrder::where('user_id', auth()->id())
            ->findOrFail($orderId)
            ->delete();
    }

    public function hasCustomOrder()
    {
        return UserTaskOrder::where('user_id', auth()->id())
            ->where('is_custom_order', true)
            ->exists();
    }
}
