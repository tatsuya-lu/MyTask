<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTaskOrder extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'task_order',
        'is_custom_order'
    ];

    protected $casts = [
        'task_order' => 'array',
        'is_custom_order' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function hasReachedLimit($userId)
    {
        return self::where('user_id', $userId)->count() >= 10;
    }

    public function getDisplayName()
    {
        return $this->name ?? $this->created_at->format('Y/m/d H:i');
    }
}
