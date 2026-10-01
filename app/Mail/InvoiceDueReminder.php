<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceDueReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Lembrete: fatura %02d/%d vence em breve', $this->invoice->reference_month, $this->invoice->reference_year),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.due-reminder',
            with: ['invoice' => $this->invoice],
        );
    }
}
