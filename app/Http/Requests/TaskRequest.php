<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->status ?? TaskStatus::NOT_STARTED->value,
            'priority' => $this->priority ?? TaskPriority::LOW->value,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'in:low,medium,high',
            'status' => 'in:not_started,in_progress,completed',
            'progress' => 'nullable|integer|min:0|max:100',
            'due_date' => 'nullable|date_format:Y-m-d',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'priority.in' => '無効な優先度が指定されています。',
            'status.in' => '無効なステータスが指定されています。',
            'progress.integer' => '進捗は整数で指定してください。',
            'progress.min' => '進捗は0以上である必要があります。',
            'progress.max' => '進捗は100以下である必要があります。'
        ];
    }
}
