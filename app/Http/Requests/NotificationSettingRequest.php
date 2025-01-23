<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationSettingRequest extends FormRequest
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
            'email_notifications_enabled' => 'required|boolean',
            'in_app_notifications_enabled' => 'required|boolean',
            'notification_timing' => 'required|array',
            'notification_timing.*' => 'integer|min:0|max:30'
        ];
    }

    public function messages(): array
    {
        return [
            'email_notifications_enabled.required' => 'メールの通知設定は必須です。',
            'email_notifications_enabled.boolean' => '無効な設定値です。',
            'in_app_notifications_enabled.required' => 'アプリ内通知の設定は必須です。',
            'in_app_notifications_enabled.boolean' => '無効な設定値です。',
            'notification_timing.required' => '通知タイミングは必須です。',
            'notification_timing.array' => '通知タイミングは配列で指定してください。',
            'notification_timing.*.integer' => '通知タイミングは整数で指定してください。',
            'notification_timing.*.min' => '通知タイミングは0以上30以下で指定してください。',
            'notification_timing.*.max' => '通知タイミングは0以上30以下で指定してください。'
        ];
    }
}
