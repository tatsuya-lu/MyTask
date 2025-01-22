@component('mail::message')
# タスク期限通知

{{ $task->user->name }}様

タスク「{{ $task->title }}」の期限が近づいています。

@if($daysUntilDue === 0)
**本日が期限日となっております。**
@elseif($daysUntilDue === 1)
**明日が期限日となっております。**
@else
**期限まであと{{ $daysUntilDue }}日となっております。**
@endif

## タスク詳細
- タイトル: {{ $task->title }}
- 説明: {{ $task->description ?? '設定なし' }}
- 優先度: {{ $task->priority->label() }}
- ステータス: {{ $task->status->label() }}
- 期限日: {{ $task->due_date->format('Y年m月d日') }}
- 進捗状況: {{ $task->progress }}%

@if($task->tags->isNotEmpty())
## 設定タグ
@foreach($task->tags as $tag)
- {{ $tag->name }}
@endforeach
@endif

@component('mail::button', ['url' => config('app.url').'/tasks/'.$task->id, 'color' => 'primary'])
タスクを確認する
@endcomponent

{{-- @if($task->team)
このタスクは「{{ $task->team->name }}」チームで共有されています。
@endif --}}

---
※ このメールは通知設定に基づいて送信されています。
通知設定は[設定画面]({{ config('app.url').'/settings/notifications' }})からいつでも変更可能です。

よろしくお願いいたします。

{{ config('app.name') }}
@endcomponent