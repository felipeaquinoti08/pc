<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\GlpiAssetImportDataTable;
use App\Jobs\ProcessGlpiAssetImportJob;
use App\Models\GlpiAssetImport;
use App\Models\GlpiInstance;
use App\Services\Glpi\GlpiAssetImportService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GlpiAssetImportController extends Controller
{
    public function index(GlpiAssetImportDataTable $dataTable)
    {
        abort_unless(auth()->user()->can('glpi_asset_import.create'), 403);

        return $dataTable->render('admin.glpi_asset_import.index');
    }

    public function create()
    {
        abort_unless(auth()->user()->can('glpi_asset_import.create'), 403);

        return view('admin.glpi_asset_import.create', [
            'instances' => GlpiInstance::where('status', GlpiInstance::STATUS_ACTIVE)->orderBy('name')->get(),
        ]);
    }

    /**
     * Parses the CSV and runs a read-only preview against the chosen GLPI
     * instance - nothing gets created here. The parsed rows are cached
     * under a one-off token so store() doesn't need the file re-uploaded.
     */
    public function analyze(Request $request)
    {
        abort_unless(auth()->user()->can('glpi_asset_import.create'), 403);

        $validated = $request->validate([
            'glpi_instance_id' => ['required', 'exists:glpi_instances,id'],
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
        ]);

        $instance = GlpiInstance::findOrFail($validated['glpi_instance_id']);
        $service = GlpiAssetImportService::forInstance($instance);

        try {
            $parsed = $service->parseCsv($request->file('csv')->getRealPath());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Falha ao ler o CSV: ' . $e->getMessage());
        }

        if (empty($parsed['rows'])) {
            return redirect()->back()->with('error', 'Nenhuma linha válida encontrada no CSV.');
        }

        try {
            $stats = $service->analyze($parsed['rows']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Falha ao consultar o GLPI para a prévia: ' . $e->getMessage());
        }

        $token = (string) Str::uuid();
        Cache::put("glpi_import_{$token}", $parsed['rows'], now()->addMinutes(30));

        return view('admin.glpi_asset_import.preview', [
            'instance' => $instance,
            'token' => $token,
            'stats' => $stats,
            'parseErrors' => $parsed['errors'],
        ]);
    }

    /**
     * Executes the import previously analyzed - re-fetches the cached
     * parsed rows by token and hands them off to a queued job (Horizon),
     * já que um CSV de milhares de linhas facilmente estoura o tempo de
     * uma requisição HTTP comum.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('glpi_asset_import.create'), 403);

        $validated = $request->validate([
            'glpi_instance_id' => ['required', 'exists:glpi_instances,id'],
            'token' => ['required', 'string'],
        ]);

        $instance = GlpiInstance::findOrFail($validated['glpi_instance_id']);
        $cacheKey = "glpi_import_{$validated['token']}";
        $rows = Cache::get($cacheKey);

        if ($rows === null) {
            return redirect()->route('admin.glpi_asset_import.create')
                ->with('error', 'Essa prévia expirou (ou já foi importada). Envie o CSV de novo.');
        }

        $import = GlpiAssetImport::create([
            'glpi_instance_id' => $instance->id,
            'user_id' => auth()->id(),
            'status' => GlpiAssetImport::STATUS_QUEUED,
            'total_rows' => count($rows),
            'rows' => $rows,
        ]);

        ProcessGlpiAssetImportJob::dispatch($import);

        Cache::forget($cacheKey);

        return redirect()->route('admin.glpi_asset_import.show', $import)
            ->with('success', 'Importação enviada para processamento em segundo plano. Acompanhe o andamento nesta página.');
    }

    public function show(GlpiAssetImport $glpi_asset_import)
    {
        abort_unless(auth()->user()->can('glpi_asset_import.create'), 403);

        return view('admin.glpi_asset_import.result', [
            'import' => $glpi_asset_import->load('glpiInstance'),
        ]);
    }

    /**
     * Removes only this app's own import record (history/preview data) -
     * never touches the assets already created/updated on the GLPI side.
     */
    public function destroy(GlpiAssetImport $glpi_asset_import)
    {
        abort_unless(auth()->user()->can('glpi_asset_import.create'), 403);

        $glpi_asset_import->delete();

        return redirect()->route('admin.glpi_asset_import.index')
            ->with('success', 'Registro de importação removido.');
    }
}
