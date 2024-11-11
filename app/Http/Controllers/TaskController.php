<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Services\TaskService;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        $query = Task::with(['tags', 'team'])
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                    ->orWhereIn('team_id', auth()->user()->teams->pluck('id'));
            })
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->priority, function ($q, $priority) {
                return $q->where('priority', $priority);
            })
            ->when($request->team_id, function ($q, $teamId) {
                return $q->where('team_id', $teamId);
            })
            ->when($request->is_archived, function ($q) {
                return $q->where('is_archived', true);
            }, function ($q) {
                return $q->where('is_archived', false);
            });

        return response()->json($query->paginate(10));
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
        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return response()->json($task->load('tags'));
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $task = $this->taskService->updateTask($task, $request->validated());
        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $this->taskService->deleteTask($task);
        return response()->json(null, 204);
    }
}
