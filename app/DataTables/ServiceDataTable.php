<?php

namespace App\DataTables;

use App\Models\BillingPolicy;
use App\Models\Service;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ServiceDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('price', function ($row) {
                return 'R$ ' . number_format($row->price, 2, ',', '.');
            })
            ->editColumn('billing_type', function ($row) {
                return [
                    'one_time' => 'Único',
                    'monthly' => 'Mensal',
                    'yearly' => 'Anual',
                ][$row->billing_type] ?? $row->billing_type;
            })
            ->addColumn('pricing_plans', function ($row) {
                if ($row->pricingPlans->isEmpty()) {
                    return '<span class="text-muted">—</span>';
                }

                $labels = BillingPolicy::frequencies();

                return $row->pricingPlans->map(function ($plan) use ($labels) {
                    $label = $labels[$plan->frequency] ?? $plan->frequency;

                    return "{$label}: R$ " . number_format($plan->price, 2, ',', '.');
                })->implode('<br>');
            })
            ->editColumn('action', function ($row) {
                return view('admin.inc.action', [
                    'edit'   => 'admin.service.edit',
                    'delete' => 'admin.service.destroy',
                    'data'   => $row
                ]);
            })
            ->editColumn('status', function ($row) {
                return view('admin.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'admin.service.status',
                    'value' => $row->status,
                ]);
            })
            ->rawColumns(['action', 'status', 'pricing_plans']);
    }

    public function query(Service $model): QueryBuilder
    {
        return $model->newQuery()->with('pricingPlans');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('service-table')
            ->addColumn(['data' => 'name', 'title' => __('Nome'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'slug', 'title' => __('Slug'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'price', 'title' => __('Preço'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'billing_type', 'title' => __('Cobrança'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'pricing_plans', 'title' => __('Planos'), 'orderable' => false, 'searchable' => false])
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
        return 'Service_' . date('YmdHis');
    }
}
