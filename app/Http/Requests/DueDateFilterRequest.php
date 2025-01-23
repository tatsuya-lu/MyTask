<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DueDateFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:day,week,month'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'フィルター名は必須です。',
            'duration_value.required' => '期間は必須です。',
            'duration_value.min' => '期間は1以上の値を指定してください。',
            'duration_unit.required' => '期間の単位は必須です。',
            'duration_unit.in' => '無効な期間単位です。'
        ];
    }
}
