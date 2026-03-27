<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryItemTemperatureMissingMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Delivery Item Temperature Missing',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.delivery-item-temperature-missing',
        );
    }
}
