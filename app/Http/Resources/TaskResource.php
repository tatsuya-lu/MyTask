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
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'priority' => $this->priority->value,
            'priority_label' => $this->priority->label(),
            'progress' => $this->progress,
            'due_date' => $this->due_date,
            'is_archived' => $this->is_archived,
            'tags' => $this->whenLoaded('tags'),
            'team' => $this->whenLoaded('team'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
