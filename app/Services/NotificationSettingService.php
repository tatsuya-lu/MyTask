<?php

namespace App\Services;

use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Auth;

class NotificationSettingService
{
    public function getUserNotificationSettings()
    {
        $user = Auth::user();
        
        return $user->notificationSetting ?? 
            NotificationSetting::create([
                'user_id' => $user->id,
                'notification_timing' => [1, 3, 7]
            ]);
    }

    public function updateNotificationSettings(array $data)
    {
        $user = Auth::user();
        $settings = $user->notificationSetting;
        
        return tap($settings)->update($data);
    }
}