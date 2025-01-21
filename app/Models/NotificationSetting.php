<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'email_notifications_enabled',
        'in_app_notifications_enabled',
        'notification_timing'
    ];

    protected $casts = [
        'notification_timing' => 'array',
        'email_notifications_enabled' => 'boolean',
        'in_app_notifications_enabled' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
