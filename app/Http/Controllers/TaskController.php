<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'status',
            'priority',
            'team_id',
            'is_archived',
            'tag_ids',
            'start_date',
            'end_date',
            'year'
        ]);

        $query = $this->taskService->getFilteredTasks($filters);

        if ($request->boolean('paginate')) {
            $perPage = $request->input('per_page', 9);
            $tasks = $query->paginate($perPage);
            return TaskResource::collection($tasks)->additional([
                'isCustomOrder' => $this->taskService->hasCustomOrder()
            ]);
        }

        $tasks = $query->get();
        return TaskResource::collection($tasks)->additional([
            'isCustomOrder' => $this->taskService->hasCustomOrder()
        ]);
    }

    public function share(Request $request, Task $task)
    {
        $this->authorize('share', $task);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $task->shared_with = $validated['user_ids'];
        $task->save();

        return response()->json($task);
    }

    public function archive(Task $task)
    {
        $this->authorize('archive', $task);

        $task->update(['is_archived' => true]);

        return response()->json(['message' => 'タスクをアーカイブしました']);
    }

    public function store(TaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated());
        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return new TaskResource($task->load('tags'));
    }

    public function update(TaskRequest $request, Task $task)
    {
        // 明示的な認可チェック
        $this->authorize('update', $task);

        // 追加のカスタムチェック
        if ($task->is_archived) {
            return response()->json(['message' => 'アーカイブされたタスクは編集できません'], 403);
        }

        // 既存の更新処理
        $task = $this->taskService->updateTask($task, $request->validated());
        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $this->taskService->deleteTask($task);
        return response()->json(null, 204);
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'taskOrder' => 'required|array',
            'taskOrder.*' => 'integer|exists:tasks,id',
            'isCustomOrder' => 'required|boolean'
        ]);

        $result = $this->taskService->updateTaskOrder(
            $validated['taskOrder'],
            $validated['isCustomOrder']
        );

        return response()->json([
            'message' => 'タスクの並び順を更新しました',
            'data' => $result
        ]);
    }

    public function getSavedOrders()
    {
        $orders = $this->taskService->getSavedTaskOrders();
        return response()->json($orders);
    }

    public function saveOrder(Request $request)
    {
        $validated = $request->validate([
            'taskOrder' => 'required|array',
            'taskOrder.*' => 'integer|exists:tasks,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'isCustomOrder' => 'required|boolean'
        ]);

        try {
            $order = $this->taskService->saveTaskOrder(
                $validated['taskOrder'],
                $validated['isCustomOrder'],
                $validated['name'] ?? null,
                $validated['description'] ?? null
            );

            return response()->json([
                'message' => '並び順を保存しました',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateSavedOrder(Request $request, $orderId)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        $order = $this->taskService->updateSavedTaskOrder($orderId, $validated);
        return response()->json(['message' => '並び順を更新しました']);
    }

    public function deleteSavedOrder($orderId)
    {
        $this->taskService->deleteSavedTaskOrder($orderId);
        return response()->json(['message' => '並び順を削除しました']);
    }
}
