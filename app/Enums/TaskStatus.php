<?php

namespace App\Enums;

enum TaskStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    
    public function label(): string
    {
        return match($this) {
            self::NEW => '新規',
            self::IN_PROGRESS => '進行中',
            self::COMPLETED => '完了'
        };
    }
}
