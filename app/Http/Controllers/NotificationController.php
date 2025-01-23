<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);

            if (!is_numeric($perPage) || $perPage < 1 || $perPage > 100) {
                $perPage = 10;
                \Log::warning('Invalid per_page value provided', [
                    'user_id' => auth()->id(),
                    'provided_value' => $request->query('per_page')
                ]);
            }

            $result = $this->notificationService->getUserNotifications($perPage);

            return response()->json($result);
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

    public function markAsRead(Notification $notification)
    {
        try {
            $this->notificationService->markNotificationAsRead($notification);
            return response()->json($notification);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllNotificationsAsRead();
        return response()->json(['message' => 'すべての通知を既読にしました']);
    }
}
