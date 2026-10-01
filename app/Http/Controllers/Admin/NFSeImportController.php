<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingPolicy;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Setting;
use App\Services\NFSeXmlImporter;
use App\Support\BusinessDays;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Backfills a real, already-issued NFS-e (typically emitted by hand on
 * nfse.gov.br before this system existed, or as a one-off outside the
 * normal flow) into a Fatura - either attaching it to a matching existing
 * one, or creating it from scratch, customer included.
 */
class NFSeImportController extends Controller
{
    public function create()
    {
        $this->authorize('create', Invoice::class);

        return view('admin.nfse_import.create');
    }

    public function analyze(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'nfse_xml' => ['required', 'file', 'max:2048'],
        ]);

        $xml = file_get_contents($request->file('nfse_xml')->getRealPath());

        try {
            $parsed = app(NFSeXmlImporter::class)->parse($xml);
        } catch (\Throwable $e) {
            return redirect()->route('admin.nfse_import.create')->with('error', $e->getMessage());
        }

        $setting = Setting::current();
        $configuredCnpj = preg_replace('/\D/', '', (string) $setting->nfse_cnpj);

        if ($configuredCnpj && $parsed['prestador_cnpj'] !== $configuredCnpj) {
            return redirect()->route('admin.nfse_import.create')->with(
                'error',
                "O CNPJ do prestador nesse XML ({$parsed['prestador_cnpj']}) não é o CNPJ configurado ({$configuredCnpj}) - parece ser a nota de outra empresa."
            );
        }

        if (Invoice::where('nfse_chave_acesso', $parsed['chave_acesso'])->exists()) {
            $existing = Invoice::where('nfse_chave_acesso', $parsed['chave_acesso'])->first();

            return redirect()->route('admin.invoice.edit', $existing->id)->with('info', 'Esta nota já tinha sido importada - fatura aberta abaixo.');
        }

        $customer = Customer::where('document_number', $parsed['tomador']['document_number'])->first();

        // Notas antigas emitidas manualmente (EmissorWeb) tendem a ter
        // dCompet igual ao mês de emissão, não ao mês descrito no serviço
        // (mesma inconsistência vista na nota de referência: emitida em
        // julho, descrição "JUN/26") - então aqui dCompet já corresponde
        // diretamente à nossa "competência de cobrança", sem deslocar mais
        // um mês. (Notas emitidas pelo NOSSO próprio fluxo nunca passam por
        // importação - já nascem com esses campos preenchidos.)
        $referenceDate = $parsed['competencia'] ?? Carbon::today();

        // Auto-seleciona a fatura dessa competência pro cliente casado,
        // mesmo que já tenha nota (a tela avisa e deixa trocar o
        // serviço/licenciamento manualmente em vez de travar só no que foi
        // casado automaticamente).
        $matchedInvoice = $customer
            ? Invoice::where('customer_id', $customer->id)
                ->where('reference_month', $referenceDate->month)
                ->where('reference_year', $referenceDate->year)
                ->first()
            : null;

        return view('admin.nfse_import.review', [
            'xmlBase64' => base64_encode($xml),
            'parsed' => $parsed,
            'customers' => Customer::orderBy('name')->get(),
            // A tela vincula a nota a um Serviço ou Licenciamento (BillingPolicy)
            // do cliente, não a uma fatura solta escolhida de uma lista - a
            // fatura em si (existente ou nova) é resolvida a partir de
            // cliente + política + competência, ver review.blade.php.
            'billingPolicies' => BillingPolicy::with(['customer', 'billable'])->orderBy('customer_id')->get(),
            'invoices' => Invoice::orderByDesc('reference_year')->orderByDesc('reference_month')->get(),
            'matchedCustomer' => $customer,
            'matchedInvoice' => $matchedInvoice,
            'referenceDate' => $referenceDate,
            'competenciaDate' => $referenceDate->copy()->subMonthNoOverflow(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'xml_base64' => ['required', 'string'],
            'chave_acesso' => ['required', 'string'],
            'numero' => ['nullable', 'string'],
            'issued_at' => ['nullable', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'new_customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'new_customer_document_type' => ['nullable', 'in:cpf,cnpj'],
            'new_customer_document_number' => ['nullable', 'string', 'max:32'],
            'new_customer_email' => ['nullable', 'email', 'max:255'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'billing_policy_id' => ['nullable', 'exists:billing_policies,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            // "Competência" aqui é o período do serviço em si (o que o
            // usuário reconhece e edita), não o "reference_month/year" do
            // Invoice - esse é a competência DE COBRANÇA, sempre um ciclo à
            // frente (mesma regra de BillingPolicy/InvoiceGenerator: "primeiro
            // pagamento é sempre no mês subsequente"). Convertido logo abaixo.
            'competencia_month' => ['required', 'integer', 'between:1,12'],
            'competencia_year' => ['required', 'integer', 'digits:4'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'in:pending,overdue,paid,cancelled'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if (Invoice::where('nfse_chave_acesso', $validated['chave_acesso'])->exists()) {
            return redirect()->route('admin.invoice.index')->with('error', 'Esta nota já tinha sido importada antes - nada foi duplicado.');
        }

        $xml = base64_decode($validated['xml_base64']);

        $invoice = DB::transaction(function () use ($request, $validated, $xml) {
            $customer = $request->filled('customer_id')
                ? Customer::findOrFail($validated['customer_id'])
                : Customer::create([
                    'name' => $validated['new_customer_name'],
                    'document_type' => $request->input('new_customer_document_type'),
                    'document_number' => $request->input('new_customer_document_number'),
                    'email' => $request->input('new_customer_email'),
                    'status' => 1,
                    'notes' => 'Cliente criado automaticamente ao importar nota fiscal antiga.',
                    'created_by_id' => Auth::id(),
                ]);

            $dueDate = Carbon::parse($validated['due_date']);

            $competenciaDate = Carbon::createFromDate($validated['competencia_year'], $validated['competencia_month'], 1);
            $referenceDate = $competenciaDate->copy()->addMonthNoOverflow();

            $billingPolicyId = $request->input('billing_policy_id') ?: null;
            $licenseId = null;

            if ($billingPolicyId) {
                $policy = BillingPolicy::with('billable')->find($billingPolicyId);
                $licenseId = $policy?->billable instanceof License ? $policy->billable_id : null;
            }

            $invoiceData = [
                'customer_id' => $customer->id,
                'billing_policy_id' => $billingPolicyId,
                'license_id' => $licenseId,
                'reference_month' => $referenceDate->month,
                'reference_year' => $referenceDate->year,
                'amount' => $validated['amount'],
                'due_date' => $dueDate,
                'grace_period_ends_at' => BusinessDays::addBusinessDays($dueDate, 5),
                'status' => $validated['status'],
                'notes' => $request->input('notes'),
                'nfse_status' => Invoice::NFSE_ISSUED,
                'nfse_chave_acesso' => $validated['chave_acesso'],
                'nfse_numero' => $request->input('numero'),
                'nfse_issued_at' => $request->filled('issued_at') ? Carbon::parse($validated['issued_at']) : now(),
            ];

            if ($validated['status'] === Invoice::STATUS_PAID) {
                // Nota antiga - "agora" quase nunca é a data real do
                // pagamento. Usa o que a tela de revisão mandou; só cai
                // pra "agora" se realmente não informar nada.
                $invoiceData['paid_at'] = $request->filled('paid_at') ? Carbon::parse($request->input('paid_at')) : now();
            }

            if ($request->filled('invoice_id')) {
                $invoice = Invoice::findOrFail($validated['invoice_id']);
                $invoice->update($invoiceData);
            } else {
                $invoiceData['created_by_id'] = Auth::id();
                $invoice = Invoice::create($invoiceData);
            }

            $xmlPath = "nfse/invoices/{$invoice->id}/nfse-{$validated['chave_acesso']}.xml";
            Storage::disk('azure')->put($xmlPath, $xml);
            $invoice->update(['nfse_xml_path' => $xmlPath]);

            return $invoice;
        });

        return redirect()->route('admin.invoice.edit', $invoice->id)->with('success', 'Nota fiscal importada com sucesso.');
    }
}
