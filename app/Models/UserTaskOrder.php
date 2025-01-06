<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTaskOrder extends Model
{
    protected $fillable = [
        'user_id',
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
}
