<?php

namespace App\DataTables;

use App\Models\GlpiAssetImport;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class GlpiAssetImportDataTable extends DataTable
{
    private const STATUS_LABELS = [
        GlpiAssetImport::STATUS_QUEUED => '<span class="badge bg-secondary">Na fila</span>',
        GlpiAssetImport::STATUS_PROCESSING => '<span class="badge bg-info">Processando</span>',
        GlpiAssetImport::STATUS_COMPLETED => '<span class="badge bg-success">Concluída</span>',
        GlpiAssetImport::STATUS_FAILED => '<span class="badge bg-danger">Falhou</span>',
    ];

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('glpi_instance', fn ($row) => $row->glpiInstance->name)
            ->editColumn('user', fn ($row) => $row->user->name ?? '-')
            ->editColumn('status', fn ($row) => self::STATUS_LABELS[$row->status] ?? $row->status)
            ->editColumn('summary', function ($row) {
                if (! $row->isFinished()) {
                    return '-';
                }

                if ($row->status === GlpiAssetImport::STATUS_FAILED) {
                    return e($row->failure_reason);
                }

                $errorCount = count($row->errors ?? []);

                return "{$row->created_count} criado(s), {$row->updated_count} atualizado(s)"
                    . ($errorCount > 0 ? ", <span class=\"text-danger\">{$errorCount} erro(s)</span>" : '');
            })
            ->editColumn('created_at', fn ($row) => $row->created_at->format('d/m/Y H:i'))
            ->addColumn('action', function ($row) {
                $view = '<a href="' . route('admin.glpi_asset_import.show', $row) . '" class="edit-icon"><i data-feather="eye"></i></a>';

                $delete = view('admin.inc.action', [
                    'data' => $row,
                    'delete' => 'admin.glpi_asset_import.destroy',
                ])->render();

                return $view . $delete;
            })
            ->rawColumns(['status', 'summary', 'action']);
    }

    public function query(GlpiAssetImport $model): QueryBuilder
    {
        return $model->newQuery()->with(['glpiInstance', 'user'])->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('glpi-asset-import-table')
            ->addColumn(['data' => 'glpi_instance', 'title' => __('Instância GLPI'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'total_rows', 'title' => __('Linhas'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'status', 'title' => __('Status'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'summary', 'title' => __('Resultado'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'user', 'title' => __('Enviado por'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'created_at', 'title' => __('Data'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'action', 'title' => __('Ações'), 'orderable' => false, 'searchable' => false])
            ->minifiedAjax()
            ->orderBy(5, 'desc')
            ->selectStyleSingle()
            ->parameters([
                'language' => [
                    'emptyTable' => 'Nenhum registro encontrado',
                    'infoEmpty' => '',
                    'zeroRecords' => 'Nenhum registro encontrado',
                ],
                'drawCallback' => 'function(settings) {
                    if (settings._iRecordsDisplay === 0) {
                        $(settings.nTableWrapper).find(".dataTables_paginate").hide();
                    } else {
                        $(settings.nTableWrapper).find(".dataTables_paginate").show();
                    }
                    feather.replace();
                }',
            ]);
    }

    public function getColumns(): array
    {
        return [];
    }

    protected function filename(): string
    {
        return 'GlpiAssetImport_' . date('YmdHis');
    }
}
