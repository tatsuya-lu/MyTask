<?php

namespace App\Enums;

enum TaskStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    
    public function label(): string
    {
        return match($this) {
            self::NOT_STARTED => '新規',
            self::IN_PROGRESS => '進行中',
            self::COMPLETED => '完了'
        };
    }
}
