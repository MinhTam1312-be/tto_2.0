<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailGoogleSheet extends Mailable
{
    use Queueable, SerializesModels;
    public $text;
    public $fullname;
    /**
     * Create a new message instance.
     */
    public function __construct($text, $fullname)
    {
        $this->text = $text;
        $this->fullname = $fullname;
    }
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), 'TTO')
            ->view('google.googleSheetNotification')
            ->with(['text' => $this->text], ['fullname', $this->fullname]);
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mail Google Sheet',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'google.googleSheetNotification',
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
