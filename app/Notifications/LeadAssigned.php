<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class LeadAssigned extends Notification
{
    use Queueable;

    protected $lead;
    protected $assignedBy;

    public function __construct(Lead $lead, User $assignedBy)
    {
        $this->lead = $lead;
        $this->assignedBy = $assignedBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'lead_id'       => $this->lead->id,
            'lead_name'     => $this->lead->name,
            'assigned_by'   => $this->assignedBy->name,
            'message'       => "A new lead '{$this->lead->name}' was assigned to you by {$this->assignedBy->name}.",
        ];
    }
}
