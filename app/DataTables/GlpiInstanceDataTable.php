<?php

namespace App\DataTables;

use App\Models\GlpiInstance;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class GlpiInstanceDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($row) {
                return view('admin.inc.action', [
                    'edit'   => 'admin.glpi_instance.edit',
                    'delete' => 'admin.glpi_instance.destroy',
                    'data'   => $row
                ]);
            })
            ->editColumn('status', function ($row) {
                return view('admin.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'admin.glpi_instance.status',
                    'value' => $row->status,
                ]);
            })
            ->rawColumns(['action', 'status']);
    }

    public function query(GlpiInstance $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('glpi-instance-table')
            ->addColumn(['data' => 'name', 'title' => __('Nome'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'base_url', 'title' => __('URL'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'username', 'title' => __('Usuário Técnico'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'status', 'title' => __('Status'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'action', 'title' => __('Ações'), 'orderable' => false, 'searchable' => false])
            ->minifiedAjax()
            ->orderBy(1)
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
        return 'GlpiInstance_' . date('YmdHis');
    }
}
