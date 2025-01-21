<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DueDateFilterController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 認証関連のルート
Route::prefix('auth')->group(function () {
    // 未認証ユーザー用
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // 認証済みユーザー用
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile'])->name('auth.profile');
    });
});

// 認証が必要なルート
Route::middleware('auth:sanctum')->group(function () {
    // タスク関連
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/', [TaskController::class, 'store'])->name('tasks.store');

        Route::put('/order', [TaskController::class, 'updateOrder'])->name('tasks.updateOrder');
        Route::get('/orders', [TaskController::class, 'getSavedOrders']);
        Route::post('/orders', [TaskController::class, 'saveOrder']);
        Route::put('/orders/{order}', [TaskController::class, 'updateSavedOrder']);
        Route::delete('/orders/{order}', [TaskController::class, 'deleteSavedOrder']);

        Route::get('/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::put('/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // タスクの共有
        Route::post('/{task}/share', [TaskController::class, 'share'])
            ->name('tasks.share');

        // タスクのアーカイブ
        Route::post('/{task}/archive', [TaskController::class, 'archive'])
            ->name('tasks.archive');
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::put('/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.markAsRead');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.markAllAsRead');
    });

    // 通知設定関連のルート
    Route::get('/notification-settings', [NotificationSettingController::class, 'show']);
    Route::put('/notification-settings', [NotificationSettingController::class, 'update']);

    // タグ関連
    // Route::apiResource('tags', TagController::class);
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('tags.index');
        Route::post('/', [TagController::class, 'store'])->name('tags.store');
        Route::put('/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    });

    // カレンダー関連
    Route::get('/calendar/tasks', [TaskController::class, 'calendarTasks'])
        ->name('tasks.calendar');

    // ユーザー検索
    Route::get('/users/search', [UserController::class, 'search']);

    // ロール一覧
    Route::get('/roles', [RoleController::class, 'index']);

    Route::get('/due-date-filters', [DueDateFilterController::class, 'index']);
    Route::post('/due-date-filters', [DueDateFilterController::class, 'store']);
    Route::delete('/due-date-filters/{dueDateFilter}', [DueDateFilterController::class, 'destroy']);
});

// チーム関連のルート
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('teams.index');
        Route::post('/', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::put('/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

        // チームメンバー管理
        Route::post('/{team}/members', [TeamController::class, 'addMember'])
            ->name('teams.members.add');
        Route::delete('/{team}/members/{user}', [TeamController::class, 'removeMember'])
            ->name('teams.members.remove');

        // チームリーダー変更
        Route::put('/{team}/leader', [TeamController::class, 'changeLeader'])
            ->name('teams.leader.change');
    });
});
