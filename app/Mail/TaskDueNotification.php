<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;

class TaskDueNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $task;
    public $daysUntilDue;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, int $daysUntilDue)
    {
        $this->task = $task;
        $this->daysUntilDue = $daysUntilDue;
    }

    public function build()
    {
        return $this->markdown('emails.tasks.due-notification')
            ->subject("タスク「{$this->task->title}」の期限が近づいています");
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "タスク「{$this->task->title}」の期限が近づいています"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tasks.due-notification',
            with: [
                'task' => $this->task,
                'daysUntilDue' => $this->daysUntilDue,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
