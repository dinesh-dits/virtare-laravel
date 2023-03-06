<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class NotificationEvent extends Event
{

    use  InteractsWithSockets, SerializesModels;

    /**
     * The patient instance.
     *
     */
    public $notification;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($notification)
    {
        //  print_r($notification);
        $this->notification = $notification;
    }
}
