<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryItemTemperatureAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $alert)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Delivery Item Temperature Alert',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.delivery-item-temperature-alert',
        );
    }
}
