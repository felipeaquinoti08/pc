<?php

namespace App\Console\Commands;

use App\Jobs\CheckInvoicePaymentJob;
use App\Models\Invoice;
use Illuminate\Console\Command;

class PollPendingInvoicePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:poll-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches a Mercado Pago reconciliation check for every pending/overdue invoice that has a real payment link - backup path in case a webhook is ever missed.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $invoices = Invoice::whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_OVERDUE])
            ->whereNotNull('mp_preference_id')
            ->get();

        foreach ($invoices as $invoice) {
            CheckInvoicePaymentJob::dispatch($invoice);
        }

        $this->info("Verificação de pagamento enfileirada para {$invoices->count()} fatura(s).");
    }
}
