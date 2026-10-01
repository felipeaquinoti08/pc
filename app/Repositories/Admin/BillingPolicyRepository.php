<?php

namespace App\Repositories\Admin;

use App\Models\BillingPolicy;
use App\Models\Customer;
use App\Models\License;
use App\Models\Proposal;
use App\Models\Service;
use App\Services\InvoiceGenerator;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class BillingPolicyRepository extends BaseRepository
{
    private const BILLABLE_TYPES = [
        'license' => License::class,
        'service' => Service::class,
        // Não selecionável no formulário (só listado aqui pra que salvar o
        // form de edição de uma política gerada automaticamente por uma
        // proposta aceita não apague esse vínculo) - ver
        // App\Repositories\Admin\ProposalRepository::generateProjectInvoices().
        'proposal' => Proposal::class,
    ];

    function model()
    {
        return BillingPolicy::class;
    }

    public function index()
    {
        return view('admin.billing_policy.index');
    }

    public function create($attribute = [])
    {
        return view('admin.billing_policy.create', $this->formData());
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $policy = $this->model->create([
                'customer_id' => $request->customer_id,
                'billable_type' => self::BILLABLE_TYPES[$request->billable_type] ?? null,
                'billable_id' => $request->billable_type ? $request->billable_id : null,
                'description' => $request->description,
                'frequency' => $request->frequency,
                'amount' => $request->amount,
                'due_day' => $request->due_day,
                'starts_at' => $request->starts_at,
                'status' => $request->status,
                'notes' => $request->notes,
                'created_by_id' => Auth::id(),
            ]);

            app(InvoiceGenerator::class)->reconcile($policy, Carbon::today());

            DB::commit();
            return redirect()->route('admin.billing_policy.index')->with('success', __('Política de faturamento cadastrada com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        return view('admin.billing_policy.edit', array_merge(
            ['policy' => $this->model->with('billable')->findOrFail($id)],
            $this->formData()
        ));
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $policy = $this->model->findOrFail($id);
            $policy->update([
                'customer_id' => $request->customer_id,
                'billable_type' => self::BILLABLE_TYPES[$request->billable_type] ?? null,
                'billable_id' => $request->billable_type ? $request->billable_id : null,
                'description' => $request->description,
                'frequency' => $request->frequency,
                'amount' => $request->amount,
                'due_day' => $request->due_day,
                'starts_at' => $request->starts_at,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            DB::commit();
            return redirect()->route('admin.billing_policy.index')->with('success', __('Política de faturamento atualizada com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * "Validar Faturas" button - reconciles this one policy on demand
     * instead of waiting for tomorrow's job run.
     */
    public function reconcileInvoices($id)
    {
        $policy = $this->model->findOrFail($id);
        $created = app(InvoiceGenerator::class)->reconcile($policy, Carbon::today());

        $message = $created->isEmpty()
            ? __('Nenhuma fatura pendente — tudo em dia.')
            : __(':count fatura(s) gerada(s): competência(s) :periods.', [
                'count' => $created->count(),
                'periods' => $created->map(fn ($invoice) => sprintf('%02d/%d', $invoice->reference_month, $invoice->reference_year))->implode(', '),
            ]);

        return redirect()->back()->with('success', $message);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $policy = $this->model->findOrFail($id);
            $policy->delete();

            DB::commit();
            return redirect()->back()->with('success', __('Política de faturamento removida com sucesso.'));
        } catch (Exception $e) {
            DB::rollback();
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'licenses' => License::with(['customer', 'service'])->orderBy('id', 'desc')->get(),
            'services' => Service::orderBy('name')->get(),
        ];
    }
}
