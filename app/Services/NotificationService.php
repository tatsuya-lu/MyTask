<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function getUserNotifications($perPage = 10)
    {
        try {
            $user = Auth::user();

            $notifications = $user->notifications()
                ->with('task')
                ->latest()
                ->paginate($perPage);

            $unreadCount = $user->notifications()
                ->where('is_read', false)
                ->count();

            Log::info('Fetching notifications', [
                'user_id' => $user->id,
                'count' => $notifications->count(),
                'unread_count' => $unreadCount
            ]);

            return [
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching notifications', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            throw $e;
        }
    }

    public function markNotificationAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            throw new \Exception('権限がありません', 403);
        }

        return $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    public function markAllNotificationsAsRead()
    {
        return Auth::user()->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }
}
