<?php

namespace App\Jobs;

use App\Mail\InvoicePaid;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\MercadoPagoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckInvoicePaymentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
    }

    /**
     * Backup path for when the webhook never arrives: asks Mercado Pago
     * directly (by external_reference, since a still-pending invoice has no
     * payment id yet) whether it has an approved payment on file.
     */
    public function handle(MercadoPagoService $mercadoPago): void
    {
        $invoice = $this->invoice->fresh();

        if (! $invoice || $invoice->status === Invoice::STATUS_PAID) {
            return;
        }

        $found = $mercadoPago->findApprovedPayment($invoice);

        if (! $found) {
            return;
        }

        [$payment, $isTest] = $found;

        $invoice->markAsPaid((string) $payment->id, $isTest);

        $customer = $invoice->license?->customer;

        if (Setting::current()->mailConfigured() && $customer?->email) {
            try {
                Mail::to($customer->email)->send(new InvoicePaid($invoice));
            } catch (\Throwable $e) {
                Log::warning("Varredura de pagamentos: falha ao enviar e-mail de pagamento confirmado para a fatura {$invoice->id}: {$e->getMessage()}");
            }
        }
    }
}
