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
            'tag_ids'
        ]);

        $query = $this->taskService->getFilteredTasks($filters);

        $perPage = $request->input('per_page', 10);
        return TaskResource::collection($query->paginate($perPage));
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
}
