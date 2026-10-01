<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\NFSeService;
use Illuminate\Console\Command;

class FetchPendingNfsePdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfse:fetch-pdfs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retries fetching the DANFSe PDF for every issued invoice that still doesn\'t have one - the ADN government endpoint is flaky enough (503s, hangs) that "Emitir Nota Fiscal" succeeding doesn\'t guarantee the PDF came with it.';

    public function handle(NFSeService $service): void
    {
        $invoices = Invoice::where('nfse_status', Invoice::NFSE_ISSUED)
            ->whereNull('nfse_pdf_path')
            ->get();

        $found = 0;

        foreach ($invoices as $invoice) {
            try {
                if ($service->refreshPdf($invoice)) {
                    $found++;
                }
            } catch (\Throwable $e) {
                // refreshPdf() itself already only throws for a state this
                // query already filters out (not issued / no chave) - this
                // is just a safety net so one bad invoice never stops the
                // rest of the sweep.
                report($e);
            }
        }

        $this->info("PDFs obtidos nesta rodada: {$found}/{$invoices->count()} fatura(s) pendente(s).");
    }
}
