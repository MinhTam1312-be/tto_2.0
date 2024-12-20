<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Certificate extends Mailable
{
    use Queueable, SerializesModels;

    public $link;
    public $fullname;
    public $date;
    public $courseName;

    
    /**
     * Create a new message instance.
     */
    public function __construct($link, $fullname, $date, $courseName)
    {
        $this->link = $link;
        $this->fullname = $fullname;
        $this->date = $date;
        $this->courseName = $courseName;
    }
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), 'TTO')
            ->view('certificate_template')
            ->with([
                'link' => $this->link,
                'fullname' => $this->fullname,
                'date' => $this->date,
                'courseName' => $this->courseName,
            ]);

    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Chứng chỉ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'certificate_template',
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
