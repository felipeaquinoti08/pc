<?php

namespace App\Console\Commands;

use App\Mail\InvoiceDueReminder;
use App\Mail\LicenseSuspended;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SweepInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:sweep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flags overdue invoices, sends due-date reminders, and suspends licenses whose invoices are unpaid past the grace period.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $today = Carbon::today();

        $this->sendReminders($today);
        $overdueCount = $this->flagOverdue($today);
        $suspendedCount = $this->suspendPastGracePeriod($today);

        $this->info("Faturas marcadas como atrasadas: {$overdueCount}. Licenças suspensas por falta de pagamento: {$suspendedCount}.");
    }

    private function sendReminders(Carbon $today): void
    {
        $reminderDate = $today->copy()->addDays(3);

        Invoice::where('status', Invoice::STATUS_PENDING)
            ->whereDate('due_date', $reminderDate)
            ->with('customer')
            ->get()
            ->each(function (Invoice $invoice) {
                $customer = $invoice->customer;

                if (! Setting::current()->mailConfigured() || ! $customer?->email) {
                    return;
                }

                try {
                    Mail::to($customer->email)->send(new InvoiceDueReminder($invoice));
                } catch (\Throwable $e) {
                    Log::warning("Faturas: falha ao enviar lembrete de vencimento para a fatura {$invoice->id}: {$e->getMessage()}");
                }
            });
    }

    private function flagOverdue(Carbon $today): int
    {
        return Invoice::where('status', Invoice::STATUS_PENDING)
            ->whereDate('due_date', '<', $today)
            ->update(['status' => Invoice::STATUS_OVERDUE]);
    }

    private function suspendPastGracePeriod(Carbon $today): int
    {
        $invoices = Invoice::where('status', Invoice::STATUS_OVERDUE)
            ->whereDate('grace_period_ends_at', '<=', $today)
            ->with(['license', 'customer'])
            ->get();

        $count = 0;

        foreach ($invoices as $invoice) {
            $license = $invoice->license;

            if (! $license || $license->suspended_invoice_id === $invoice->id) {
                continue;
            }

            $license->status = License::STATUS_SUSPENDED;
            $license->suspended_invoice_id = $invoice->id;
            $license->save();

            $count++;

            $customer = $invoice->customer;

            if (! Setting::current()->mailConfigured() || ! $customer?->email) {
                Log::info("Faturas: e-mail de suspensão não enviado para a licença {$license->id} (e-mail não configurado ou cliente sem e-mail).");

                continue;
            }

            try {
                Mail::to($customer->email)->send(new LicenseSuspended($invoice));
            } catch (\Throwable $e) {
                Log::warning("Faturas: falha ao enviar e-mail de suspensão para a licença {$license->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }
}
