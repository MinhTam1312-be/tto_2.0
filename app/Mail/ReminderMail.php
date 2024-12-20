<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $course;
    public $fullname;

    // Nhận đối tượng Reminder từ Job
    public function __construct($course, $fullname)
    {
        $this->course = $course;
        $this->fullname = $fullname;
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), 'TTO')
            ->subject('Reminder Notification')
            ->view('emails.reminder')
            ->with([
                'course' => $this->course,
                'fullname' => $this->fullname,
            ]);;
    }
}
