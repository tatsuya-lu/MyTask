<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);

            $notifications = auth()->user()->notifications()
                ->with('task') // タスク情報も一緒に取得
                ->latest()
                ->paginate($perPage);

            $unreadCount = auth()->user()->notifications()
                ->where('is_read', false)
                ->count();

            \Log::info('Fetching notifications', [
                'user_id' => auth()->id(),
                'count' => $notifications->count(),
                'unread_count' => $unreadCount
            ]);

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching notifications', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => '通知の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => '権限がありません'], 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json($notification);
    }

    public function markAllAsRead()
    {
        auth()->user()->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['message' => 'すべての通知を既読にしました']);
    }
}
