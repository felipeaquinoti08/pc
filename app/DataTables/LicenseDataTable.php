<?php

namespace App\DataTables;

use App\Models\License;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class LicenseDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('customer_name', fn ($row) => $row->customer?->name)
            ->addColumn('service_name', fn ($row) => $row->service?->name)
            ->addColumn('usage', function ($row) {
                $active = $row->activeActivations()->count();
                $class = $active >= $row->quantity ? 'text-danger' : 'text-success';
                return "<span class=\"{$class}\">{$active}/{$row->quantity}</span>";
            })
            ->editColumn('expires_at', function ($row) {
                return $row->expires_at ? $row->expires_at->format('d/m/Y') : 'Sem vencimento';
            })
            ->editColumn('status', function ($row) {
                $labels = [
                    'active' => '<span class="badge bg-success">Ativa</span>',
                    'expired' => '<span class="badge bg-secondary">Expirada</span>',
                    'suspended' => '<span class="badge bg-warning">Suspensa</span>',
                    'cancelled' => '<span class="badge bg-danger">Cancelada</span>',
                ];
                return $labels[$row->status] ?? $row->status;
            })
            ->editColumn('action', function ($row) {
                return view('admin.inc.action', [
                    'edit'   => 'admin.license.edit',
                    'delete' => 'admin.license.destroy',
                    'data'   => $row
                ]);
            })
            ->rawColumns(['usage', 'status', 'action']);
    }

    public function query(License $model): QueryBuilder
    {
        return $model->newQuery()->with(['customer', 'service', 'activations']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('license-table')
            ->addColumn(['data' => 'license_key', 'title' => __('Chave'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'customer_name', 'title' => __('Cliente'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'service_name', 'title' => __('Serviço'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'usage', 'title' => __('Instalações'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'expires_at', 'title' => __('Vencimento'), 'orderable' => true, 'searchable' => false])
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
        return 'License_' . date('YmdHis');
    }
}
