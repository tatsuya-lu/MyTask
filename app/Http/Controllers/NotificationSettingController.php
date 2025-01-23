<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationSettingRequest;
use App\Services\NotificationSettingService;

class NotificationSettingController extends Controller
{
    protected $notificationSettingService;

    public function __construct(NotificationSettingService $notificationSettingService)
    {
        $this->notificationSettingService = $notificationSettingService;
    }

    public function show()
    {
        $settings = $this->notificationSettingService->getUserNotificationSettings();
        return response()->json($settings);
    }

    public function update(NotificationSettingRequest $request)
    {
        $settings = $this->notificationSettingService->updateNotificationSettings(
            $request->validated()
        );

        return response()->json($settings);
    }
}