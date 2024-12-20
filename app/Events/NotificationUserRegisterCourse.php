<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationUserRegisterCourse implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $course;
    public $user;
    /**
     * Create a new event instance.
     */
    
    public function __construct($user, $course)
    {
        $this->user = $user;
        $this->course = $course;
    }

    /**
     * Get the channels the event should broadcast on.  
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return ['course-register'];
    }
    public function broadcasAS()
    {
        return 'my-event';
    }
}
