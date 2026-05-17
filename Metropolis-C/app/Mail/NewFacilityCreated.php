<?php

namespace App\Mail;

use App\Models\Facility;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewFacilityCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Facility $facility) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('wouterver10@gmail.com', 'Metropolis'),
            subject: 'New Function Added: ' . $this->facility->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_facility_created',
        );
    }
}
