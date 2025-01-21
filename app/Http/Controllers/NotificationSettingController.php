<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function show()
    {
        $settings = auth()->user()->notificationSetting ?? 
            NotificationSetting::create([
                'user_id' => auth()->id(),
                'notification_timing' => [1, 3, 7]
            ]);

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'email_notifications_enabled' => 'required|boolean',
            'in_app_notifications_enabled' => 'required|boolean',
            'notification_timing' => 'required|array',
            'notification_timing.*' => 'integer|min:0|max:30'
        ]);

        $settings = auth()->user()->notificationSetting;
        $settings->update($validated);

        return response()->json($settings);
    }
}