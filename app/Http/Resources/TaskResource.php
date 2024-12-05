<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value, // statusの値を直接取得
            'status_label' => $this->status->label(), // 既存のstatus列挙型インスタンスから直接ラベルを呼び出し
            'priority' => $this->priority->value, // priorityも同様に
            'priority_label' => $this->priority->label(), // 既存のpriority列挙型インスタンスから直接ラベルを呼び出し
            'due_date' => $this->due_date,
            'is_archived' => $this->is_archived,
            'tags' => $this->whenLoaded('tags'),
            'team' => $this->whenLoaded('team'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
