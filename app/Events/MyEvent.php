<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class MyEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
        // Log::info("MyEvent created with message: {$message}");
    }

    public function broadcastOn()
    {
        Log::info("Broadcasting on channel: my-channel");
        return ['my-channel'];
    }

    public function broadcastAs()
    {
        return 'my-event';
    }
}
