<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\InvoiceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Repositories\Admin\InvoiceRepository;
use App\Services\NFSeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public $repository;

    public function __construct(InvoiceRepository $repository)
    {
        $this->authorizeResource(Invoice::class, 'invoice');
        $this->repository = $repository;
    }

    public function index(InvoiceDataTable $dataTable)
    {
        return $dataTable->render('admin.invoice.index');
    }

    public function show(Invoice $invoice)
    {
        //
    }

    public function edit(Invoice $invoice)
    {
        return $this->repository->edit($invoice->id);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        return $this->repository->update($request, $invoice->id);
    }

    public function destroy(Invoice $invoice)
    {
        return $this->repository->destroy($invoice->id);
    }

    public function markPaid(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        return $this->repository->markPaid($invoice->id);
    }

    public function regenerateLink(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        return $this->repository->regenerateLink($invoice->id);
    }

    public function issueNfse(Request $request, Invoice $invoice, NFSeService $service)
    {
        $this->authorize('update', $invoice);

        $competencia = null;

        if ($request->filled('competencia_month') && $request->filled('competencia_year')) {
            $validated = $request->validate([
                'competencia_month' => ['integer', 'between:1,12'],
                'competencia_year' => ['integer', 'digits:4'],
            ]);

            $competencia = \Illuminate\Support\Carbon::createFromDate($validated['competencia_year'], $validated['competencia_month'], 1);
        }

        try {
            $service->issue($invoice, $competencia);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $invoice->refresh();

        if ($invoice->nfse_status === Invoice::NFSE_ISSUED) {
            return redirect()->back()->with('success', __('Nota Fiscal emitida com sucesso.'));
        }

        // Full detail already renders in the dedicated alert box on this
        // page (see admin/invoice/edit.blade.php) - the toast just needs to
        // say "something's wrong, look below", not repeat a possibly-long
        // government error message inside a 300px-wide popup.
        return redirect()->back()->with('error', __('Falha ao emitir a Nota Fiscal — veja o detalhe abaixo.'));
    }

    public function downloadNfseXml(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if (! $invoice->nfse_xml_path || ! Storage::disk('azure')->exists($invoice->nfse_xml_path)) {
            return redirect()->back()->with('error', __('XML da Nota Fiscal não encontrado.'));
        }

        return Storage::disk('azure')->download($invoice->nfse_xml_path, "nfse-{$invoice->nfse_chave_acesso}.xml");
    }

    public function refreshNfsePdf(Invoice $invoice, NFSeService $service)
    {
        $this->authorize('update', $invoice);

        try {
            $found = $service->refreshPdf($invoice);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        if (! $found) {
            return redirect()->back()->with('error', __('O portal do governo não retornou o PDF agora (instabilidade comum no ADN). Se ele já aparece pra download no site do nfse.gov.br, baixe de lá e envie o arquivo logo abaixo.'));
        }

        return redirect()->back()->with('success', __('PDF da Nota Fiscal obtido com sucesso.'));
    }

    /**
     * Fallback pra quando "Buscar PDF" falha por instabilidade do ADN mas a
     * nota já está disponível pro contribuinte baixar manualmente em
     * nfse.gov.br - ver NFSeService::storeManualPdf().
     */
    public function uploadNfsePdf(Request $request, Invoice $invoice, NFSeService $service)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        try {
            $service->storeManualPdf($invoice, $request->file('pdf'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('PDF da Nota Fiscal enviado com sucesso.'));
    }

    public function downloadNfsePdf(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if (! $invoice->nfse_pdf_path || ! Storage::disk('azure')->exists($invoice->nfse_pdf_path)) {
            return redirect()->back()->with('error', __('PDF da Nota Fiscal não encontrado.'));
        }

        return Storage::disk('azure')->download($invoice->nfse_pdf_path, "nfse-{$invoice->nfse_chave_acesso}.pdf");
    }
}
