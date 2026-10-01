<?php

namespace App\Mail;

use App\Models\ContactLead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactLeadReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactLead $contactLead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo contato pelo site - ' . $this->contactLead->name,
            replyTo: [$this->contactLead->email => $this->contactLead->name],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-leads.received',
            with: ['contactLead' => $this->contactLead],
        );
    }
}
