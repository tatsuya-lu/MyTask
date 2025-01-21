@component('mail::message')
# タスク期限通知

{{ $task->user->name }}様

タスク「{{ $task->title }}」の期限が近づいています。

@if($daysUntilDue === 0)
**本日が期限日です。**
@else
**期限まであと{{ $daysUntilDue }}日です。**
@endif

## タスク詳細
- タイトル: {{ $task->title }}
- 説明: {{ $task->description }}
- 期限日: {{ $task->due_date->format('Y年m月d日') }}
- 進捗状況: {{ $task->progress }}%

@component('mail::button', ['url' => config('app.url').'/tasks/'.$task->id])
タスクを確認する
@endcomponent

このメールは通知設定に基づいて送信されています。
通知設定は設定画面からいつでも変更可能です。

よろしくお願いいたします。<br>
{{ config('app.name') }}
@endcomponent