<?php
// app/Notifications/AnnouncementNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AnnouncementNotification extends Notification
{
    use Queueable;

    protected $announcement;

    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'announcement_id' => $this->announcement->id,
            'title'           => $this->announcement->title,
            'body'            => \Illuminate\Support\Str::limit($this->announcement->body, 80),
            'url'             => route('announcements.show', $this->announcement->id),
        ];
    }
}
