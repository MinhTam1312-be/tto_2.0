<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class payMent extends Mailable
{
    use Queueable, SerializesModels;
    public $fullname;
    public $nameCourse;
    public $priceCourse;
    public $date;
    /**
     * Create a new message instance.
     */
    public function __construct($fullname, $nameCourse, $priceCourse, $date)
    {
        $this->fullname = $fullname;
        $this->nameCourse = $nameCourse;
        $this->priceCourse = $priceCourse;
        $this->date = $date;
    }
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), 'TTO')
            ->view('payment.gmailPayment')
            ->with(
                ['fullname' => $this->fullname],
                ['nameCourse' => $this->nameCourse],
                ['priceCourse' => number_format($this->priceCourse / 100, 0, ',', '.') . ' VND'],
                ['date' => $this->date],
            );
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thành toán khóa học',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'payment.gmailPayment',
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
