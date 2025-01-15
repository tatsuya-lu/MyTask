<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DueDateFilter extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'duration_value',
        'duration_unit'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
