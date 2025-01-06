<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'progress',
        'due_date',
        'is_archived',
        'sort_order'
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'sort_order' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_task')
            ->withTimestamps();
    }

    public function scopeCustomOrder($query, $userId)
    {
        $userOrder = UserTaskOrder::where('user_id', $userId)
            ->where('is_custom_order', true)
            ->first();

        if ($userOrder) {
            return $query->orderByRaw(
                'FIELD(id,' . implode(',', $userOrder->task_order) . ')'
            );
        }

        return $query->orderBy('sort_order');
    }
}
