<?php

namespace App\Services;

use App\Mail\InvoiceCreated;
use App\Models\BillingPolicy;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Setting;
use App\Support\BusinessDays;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Turns a BillingPolicy into actual Invoice rows. The single entry point,
 * reconcile(), is shared by the daily `invoices:generate` command, the
 * "Validar Faturas" button on a policy's edit screen, and
 * BillingPolicyRepository right after a policy is created - all three just
 * mean "make sure this policy's invoices are caught up to today".
 */
class InvoiceGenerator
{
    /**
     * Generates whatever invoices this policy is missing, oldest first.
     * Safe to call repeatedly - already-generated periods are skipped.
     * Returns the invoices actually created (empty if already up to date).
     */
    public function reconcile(BillingPolicy $policy, Carbon $today): Collection
    {
        if ($policy->status !== BillingPolicy::STATUS_ACTIVE) {
            return collect();
        }

        if ($policy->frequency === BillingPolicy::FREQUENCY_ONCE) {
            if ($policy->invoices()->withTrashed()->exists()) {
                return collect();
            }

            return collect([$this->create($policy, $today->month, $today->year, $policy->dueDateForPeriod($today))]);
        }

        $created = collect();

        foreach ($policy->dueDates($today) as $periodStart) {
            // withTrashed(): the (billing_policy_id, reference_month, reference_year)
            // unique constraint isn't a partial index, so it still blocks inserts
            // against a soft-deleted invoice's period. A deleted invoice still means
            // this period was already billed, so it must count as "exists" here too,
            // or this throws a duplicate-key exception instead of just skipping.
            $exists = Invoice::withTrashed()
                ->where('billing_policy_id', $policy->id)
                ->where('reference_month', $periodStart->month)
                ->where('reference_year', $periodStart->year)
                ->exists();

            if ($exists) {
                continue;
            }

            $dueDate = $policy->dueDateForPeriod($periodStart);

            $created->push($this->create($policy, $periodStart->month, $periodStart->year, $dueDate));
        }

        return $created;
    }

    /**
     * One-shot invoice for a `once` policy whose due date isn't "some day
     * this month" (the assumption reconcile() makes, via
     * dueDateForPeriod($today)) but a specific, possibly far-future date -
     * e.g. a proposal's down/final payment, where the final payment is due
     * ~30 days after project completion. Idempotent per policy, same rule
     * as reconcile()'s `once` branch: a policy that already has an invoice
     * (even soft-deleted) is left alone.
     */
    public function createForDate(BillingPolicy $policy, Carbon $dueDate): ?Invoice
    {
        if ($policy->invoices()->withTrashed()->exists()) {
            return null;
        }

        return $this->create($policy, $dueDate->month, $dueDate->year, $dueDate);
    }

    private function create(BillingPolicy $policy, int $month, int $year, Carbon $dueDate): Invoice
    {
        $invoice = Invoice::create([
            'license_id' => $policy->billable_type === License::class ? $policy->billable_id : null,
            'customer_id' => $policy->customer_id,
            'billing_policy_id' => $policy->id,
            'reference_month' => $month,
            'reference_year' => $year,
            'amount' => $policy->amount,
            'due_date' => $dueDate,
            'grace_period_ends_at' => BusinessDays::addBusinessDays($dueDate, 5),
            'status' => Invoice::STATUS_PENDING,
            'notes' => $this->serviceDescription($policy, $month, $year, $dueDate),
        ]);

        $this->attachPaymentLink($invoice);
        $this->sendCreatedNotification($invoice);

        return $invoice;
    }

    /**
     * "Descrição do Serviço" for this invoice - the invoice bills this
     * cycle (e.g. August) in advance, but the service period it actually
     * covers is the month before (July), which is what {{competencia}}
     * resolves to (and what should show up on the NFe later).
     *
     * If the policy has its own template (bank details, custom wording...)
     * with {{competencia}}/{{vencimento}} placeholders, that's used as-is
     * with the tokens substituted; otherwise falls back to a plain
     * auto-generated line so the field is never blank.
     */
    private function serviceDescription(BillingPolicy $policy, int $month, int $year, Carbon $dueDate): string
    {
        $servicePeriod = Carbon::createFromDate($year, $month, 1)->subMonthNoOverflow();
        $competencia = strtoupper($servicePeriod->locale('pt_BR')->isoFormat('MMM/YY'));
        $vencimento = $dueDate->format('d/m/Y');

        if (filled($policy->notes)) {
            return str_replace(
                ['{{competencia}}', '{{vencimento}}'],
                [$competencia, $vencimento],
                $policy->notes
            );
        }

        return sprintf(
            '%s — referente a %s.',
            $policy->label(),
            $servicePeriod->locale('pt_BR')->isoFormat('MMMM [de] YYYY')
        );
    }

    private function attachPaymentLink(Invoice $invoice): void
    {
        if (! Setting::current()->mercadoPagoConfigured()) {
            return;
        }

        try {
            app(MercadoPagoService::class)->attachPreference($invoice);
        } catch (\Throwable $e) {
            Log::warning("Faturas: falha ao gerar link de pagamento para a fatura {$invoice->id}: {$e->getMessage()}");
        }
    }

    private function sendCreatedNotification(Invoice $invoice): void
    {
        $customer = $invoice->customer;

        if (! Setting::current()->mailConfigured() || ! $customer?->email) {
            Log::info("Faturas: e-mail de fatura criada não enviado para a fatura {$invoice->id} (e-mail não configurado ou cliente sem e-mail).");

            return;
        }

        try {
            Mail::to($customer->email)->send(new InvoiceCreated($invoice));
        } catch (\Throwable $e) {
            Log::warning("Faturas: falha ao enviar e-mail de fatura criada para a fatura {$invoice->id}: {$e->getMessage()}");
        }
    }
}
