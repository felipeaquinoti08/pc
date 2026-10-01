<?php

namespace App\DataTables;

use App\Models\BillingPolicy;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class BillingPolicyDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('customer_name', fn ($row) => $row->customer?->name)
            ->addColumn('billable_label', fn ($row) => $row->label())
            ->editColumn('frequency', fn ($row) => $row->frequencyLabel())
            ->editColumn('amount', fn ($row) => 'R$ '.number_format($row->amount, 2, ',', '.'))
            ->editColumn('due_day', fn ($row) => 'Dia '.$row->due_day)
            ->editColumn('status', function ($row) {
                $labels = [
                    'active' => '<span class="badge bg-success">Ativa</span>',
                    'paused' => '<span class="badge bg-warning">Pausada</span>',
                    'cancelled' => '<span class="badge bg-danger">Cancelada</span>',
                ];
                return $labels[$row->status] ?? $row->status;
            })
            ->editColumn('action', function ($row) {
                return view('admin.inc.action', [
                    'edit'   => 'admin.billing_policy.edit',
                    'delete' => 'admin.billing_policy.destroy',
                    'data'   => $row
                ]);
            })
            ->rawColumns(['status', 'action']);
    }

    public function query(BillingPolicy $model): QueryBuilder
    {
        return $model->newQuery()->with(['customer', 'billable']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('billing-policy-table')
            ->addColumn(['data' => 'customer_name', 'title' => __('Cliente'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'billable_label', 'title' => __('Referente a'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'frequency', 'title' => __('Frequência'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'amount', 'title' => __('Valor'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'due_day', 'title' => __('Vencimento'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'status', 'title' => __('Status'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'action', 'title' => __('Ações'), 'orderable' => false, 'searchable' => false])
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->parameters([
                'language' => [
                    'emptyTable' => 'Nenhuma política de faturamento cadastrada',
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
        return 'BillingPolicy_' . date('YmdHis');
    }
}
