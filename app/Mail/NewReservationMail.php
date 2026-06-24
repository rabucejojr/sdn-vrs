<?php

namespace App\Mail;

use App\Models\TripTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReservationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly TripTicket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SDN-VRS] New Reservation Filed — ' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.new-reservation',
        );
    }
}
