<?php

namespace App\Mail;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payout $payout) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Received — MM Book Store',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.PayoutConfirmedMail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
