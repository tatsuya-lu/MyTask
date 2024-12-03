<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        //個人タスクの場合
        if (!$task->team_id) {
            return $user->id === $task->user_id;
        }

        //チームタスクの場合
        return $user->teams->contains($task->team_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // アーカイブされたタスクは編集不可
        if ($task->is_archived) {
            return false;
        }

        // 個人タスクの場合
        if (!$task->team_id) {
            return $user->id === $task->user_id;
        }

        // チームタスクの場合
        return $user->id === $task->user_id ||
            $user->isTeamLeader($task->team);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        if ($task->is_archived) {
            return false;
        }

        if (!$task->team_id) {
            return $user->id === $task->user_id;
        }

        return $user->isTeamLeader($task->team);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        //
    }

    public function share(User $user, Task $task)
    {
        return $user->isPremium() && $user->id === $task->user_id;
    }

    public function archive(User $user, Task $task)
    {
        return $user->id === $task->user_id &&
            $task->status === 'completed';
    }
}
