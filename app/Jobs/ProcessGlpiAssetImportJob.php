<?php

namespace App\Jobs;

use App\Models\GlpiAssetImport;
use App\Services\Glpi\GlpiAssetImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessGlpiAssetImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * CSVs de milhares de linhas fazem uma chamada HTTP ao GLPI por
     * dropdown/ativo - bem acima do timeout padrão (60s) dos workers do
     * Horizon. import() já é idempotente (otherserial/name), então uma
     * única tentativa longa é mais segura que deixar o Horizon reagendar
     * uma tentativa nova por cima de uma que só estava demorando.
     */
    public $timeout = 3600;
    public $tries = 1;

    public function __construct(public GlpiAssetImport $import)
    {
    }

    public function handle(): void
    {
        $this->import->update([
            'status' => GlpiAssetImport::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        try {
            $service = GlpiAssetImportService::forInstance($this->import->glpiInstance);
            $result = $service->import($this->import->rows);

            $this->import->update([
                'status' => GlpiAssetImport::STATUS_COMPLETED,
                'created_count' => $result['created'],
                'updated_count' => $result['updated'],
                'errors' => $result['errors'],
                'items' => $result['items'],
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Importação de ativos GLPI #{$this->import->id} falhou: {$e->getMessage()}");

            $this->import->update([
                'status' => GlpiAssetImport::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }
    }
}
