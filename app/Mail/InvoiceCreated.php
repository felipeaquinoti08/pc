<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Nova fatura %02d/%d disponível', $this->invoice->reference_month, $this->invoice->reference_year),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.created',
            with: ['invoice' => $this->invoice],
        );
    }
}
