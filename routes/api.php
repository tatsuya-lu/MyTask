<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

// 認証不要のルート
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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