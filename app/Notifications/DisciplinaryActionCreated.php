<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Discipline\InfractionReport;

class DisciplinaryActionCreated extends Notification
{
    use Queueable;

    /** @var InfractionReport */
    public $infraction;

    public function __construct(InfractionReport $infraction)
    {
        $this->infraction = $infraction;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'infraction_id' => $this->infraction->id,
            'title'         => 'You have a new infraction report (#' . $this->infraction->id . ')',
            'date'          => $this->infraction->incident_date->toDateString(),
            'location'      => $this->infraction->location,
            'description'   => \Str::limit($this->infraction->description, 100),
            // point at the NotificationController@show route
            'url'           => route('notifications.show', $notifiable->notifications()->latest()->first()->id),
        ];
    }
}
