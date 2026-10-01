<?php

namespace App\Console\Commands;

use App\Models\BillingPolicy;
use App\Services\InvoiceGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconciles every active billing policy against its invoices, generating whatever cycles are due and missing.';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceGenerator $generator): void
    {
        $today = Carbon::today();

        $policies = BillingPolicy::where('status', BillingPolicy::STATUS_ACTIVE)->get();

        $created = 0;
        $failed = 0;

        foreach ($policies as $policy) {
            try {
                $created += $generator->reconcile($policy, $today)->count();
            } catch (\Throwable $e) {
                $failed++;
                Log::error("Faturas: falha ao reconciliar a política de cobrança {$policy->id}: {$e->getMessage()}", ['exception' => $e]);
            }
        }

        $this->info("Faturas geradas: {$created} (verificação em {$today->format('d/m/Y')})." . ($failed > 0 ? " Falhas: {$failed}." : ''));
    }
}
