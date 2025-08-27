<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskAssigned extends Notification
{
    use Queueable;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['database']; // no email, only in-app
    }

public function toDatabase($notifiable)
{
    return [
        'task_id' => $this->task->id,
        'title' => $this->task->title,
        'assigned_by' => $this->task->creator->name,
        'url' => route('tasks.show', $this->task->id),
    ];
}


}
