<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogSentMessage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event)
    {
        // Ghi log khi email được gửi thành công
        Log::info('Email đã gửi thành công!', ['message' => $event->message]);
    }
}
