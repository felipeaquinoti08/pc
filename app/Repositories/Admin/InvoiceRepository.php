<?php

namespace App\Repositories\Admin;

use App\Models\BillingPolicy;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Setting;
use App\Services\MercadoPagoService;
use App\Support\BusinessDays;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Prettus\Repository\Eloquent\BaseRepository;

class InvoiceRepository extends BaseRepository
{
    function model()
    {
        return Invoice::class;
    }

    public function index()
    {
        return view('admin.invoice.index');
    }

    public function edit($id)
    {
        $invoice = $this->model->with(['customer', 'billingPolicy.billable', 'license.customer', 'license.service'])->find($id);

        return view('admin.invoice.edit', [
            'invoice' => $invoice,
            'billingPolicies' => BillingPolicy::where('customer_id', $invoice->customer_id)->with('billable')->orderBy('id')->get(),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $invoice = $this->model->findOrFail($id);
            $dueDate = Carbon::parse($request->due_date);

            // Real payment date, editable so backfilled/historical invoices
            // (e.g. reconciling a policy whose contract predates the system)
            // can be corrected to when they were actually paid instead of
            // being stuck with whatever day an admin happened to click
            // "salvar" - falls back to today only when marking newly paid
            // without picking a date, same convenience the quick
            // "Marcar como paga" button already has.
            $paidAt = $request->filled('paid_at') ? Carbon::parse($request->paid_at) : $invoice->paid_at;
            if ($request->status === Invoice::STATUS_PAID && ! $paidAt) {
                $paidAt = now();
            }

            $billingPolicyId = $request->input('billing_policy_id') ?: null;
            $licenseId = null;

            if ($billingPolicyId) {
                $policy = BillingPolicy::with('billable')->find($billingPolicyId);
                $licenseId = $policy?->billable instanceof License ? $policy->billable_id : null;
            }

            $invoice->update([
                'amount' => $request->amount,
                'due_date' => $dueDate,
                'grace_period_ends_at' => BusinessDays::addBusinessDays($dueDate, 5),
                'status' => $request->status,
                'paid_at' => $paidAt,
                'notes' => $request->notes,
                'billing_policy_id' => $billingPolicyId,
                'license_id' => $licenseId,
            ]);

            DB::commit();
            return redirect()->route('admin.invoice.index')->with('success', 'Fatura atualizada com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $invoice = $this->model->findOrFail($id);
            $invoice->delete();

            DB::commit();
            return redirect()->back()->with('success', __('Fatura removida com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function markPaid($id)
    {
        $invoice = $this->model->findOrFail($id);
        $invoice->markAsPaid();

        return redirect()->back()->with('success', __('Fatura marcada como paga. A licença foi reativada, se estava suspensa por essa fatura.'));
    }

    public function regenerateLink($id)
    {
        $invoice = $this->model->findOrFail($id);

        if (! Setting::current()->mercadoPagoConfigured()) {
            return redirect()->back()->with('error', __('Mercado Pago não está configurado (Configurações → Pagamentos).'));
        }

        try {
            $this->attachPaymentLink($invoice);
        } catch (Exception $e) {
            Log::warning("Faturas: falha ao gerar link de pagamento para a fatura {$invoice->id}: {$e->getMessage()}");

            return redirect()->back()->with('error', __('Falha ao gerar o link de pagamento: ').$e->getMessage());
        }

        return redirect()->back()->with('success', __('Link de pagamento gerado com sucesso.'));
    }

    private function attachPaymentLink(Invoice $invoice): void
    {
        if (! Setting::current()->mercadoPagoConfigured()) {
            return;
        }

        try {
            app(MercadoPagoService::class)->attachPreference($invoice);
        } catch (Exception $e) {
            Log::warning("Faturas: falha ao gerar link de pagamento para a fatura {$invoice->id}: {$e->getMessage()}");
        }
    }
}
