<?php

namespace App\DataTables;

use App\Models\Invoice;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class InvoiceDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('customer_name', fn ($row) => $row->customer?->name)
            ->addColumn('service_name', fn ($row) => $row->billingPolicy?->label() ?? '-')
            ->addColumn('reference', fn ($row) => sprintf('%02d/%d', $row->reference_month, $row->reference_year))
            ->editColumn('amount', fn ($row) => 'R$ '.number_format($row->amount, 2, ',', '.'))
            ->editColumn('due_date', fn ($row) => $row->due_date->format('d/m/Y'))
            ->addColumn('paid_at', fn ($row) => $row->paid_at?->format('d/m/Y') ?? '-')
            ->editColumn('status', function ($row) {
                $labels = [
                    'pending' => '<span class="badge bg-secondary">Pendente</span>',
                    'overdue' => '<span class="badge bg-warning">Atrasada</span>',
                    'paid' => '<span class="badge bg-success">Paga</span>',
                    'cancelled' => '<span class="badge bg-danger">Cancelada</span>',
                ];
                $badge = $labels[$row->status] ?? $row->status;

                if ($row->is_test_payment) {
                    $badge .= ' <span class="badge bg-info">TESTE</span>';
                }

                return $badge;
            })
            ->editColumn('action', function ($row) {
                return view('admin.inc.action', [
                    'edit'   => 'admin.invoice.edit',
                    'delete' => 'admin.invoice.destroy',
                    'data'   => $row
                ]);
            })
            ->rawColumns(['status', 'action']);
    }

    public function query(Invoice $model): QueryBuilder
    {
        return $model->newQuery()->with(['customer', 'billingPolicy.billable']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('invoice-table')
            ->addColumn(['data' => 'customer_name', 'title' => __('Cliente'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'service_name', 'title' => __('Referente a'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'reference', 'title' => __('Competência'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'amount', 'title' => __('Valor'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'due_date', 'title' => __('Vencimento'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'paid_at', 'title' => __('Pago em'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'status', 'title' => __('Status'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'action', 'title' => __('Ações'), 'orderable' => false, 'searchable' => false])
            ->minifiedAjax()
            ->orderBy(4)
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
        return 'Invoice_' . date('YmdHis');
    }
}
