<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sendMailToConfirmChanges extends Mailable
{
    use Queueable, SerializesModels;
    public $mailOld;
    public $mailNew;
    public $fullname;
    /**
     * Create a new message instance.
     */
    public function __construct($mailOld, $mailNew, $fullname)
    {
        $this->mailOld = $mailOld;
        $this->mailNew = $mailNew;
        $this->fullname = $fullname;
    }

    /**
     * Get the message envelope.
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), 'TTO')
            ->view('sendConfirmationMail')
            ->with(
                ['mailOld' => $this->mailOld],
                ['mailNew' => $this->mailNew],
                ['fullname' => $this->fullname]
            );
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận mail thay đổi',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'sendConfirmationMail',
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
