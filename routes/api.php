<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// 認証が必要なルート
Route::middleware('auth:sanctum')->group(function () {
    // ユーザー情報
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // タスク関連
    Route::apiResource('tasks', TaskController::class);
    
    // タグ関連
    Route::apiResource('tags', TagController::class);
    
    // カレンダー関連
    Route::get('/calendar/tasks', [TaskController::class, 'calendarTasks']);
    
    // タスクステータス更新
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::patch('/tasks/{task}/priority', [TaskController::class, 'updatePriority']);
});

Route::middleware(['auth:sanctum', 'premium'])->group(function () {
    Route::apiResource('teams', TeamController::class);
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember']);
    Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember']);
});