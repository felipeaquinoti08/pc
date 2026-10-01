<?php

namespace App\DataTables;

use App\Models\Proposal;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ProposalDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('customer_name', fn ($row) => $row->customer?->name)
            ->editColumn('total_value', fn ($row) => 'R$ '.number_format($row->total_value, 2, ',', '.'))
            ->editColumn('proposal_year', fn ($row) => $row->title.' · '.$row->proposal_year)
            ->editColumn('status', function ($row) {
                $labels = [
                    'draft' => '<span class="badge bg-secondary">Rascunho</span>',
                    'sent' => '<span class="badge bg-info">Enviada</span>',
                    'accepted' => '<span class="badge bg-success">Aceita</span>',
                    'rejected' => '<span class="badge bg-danger">Recusada</span>',
                ];
                return $labels[$row->status] ?? $row->status;
            })
            ->editColumn('action', function ($row) {
                return view('admin.inc.action', [
                    'edit'   => 'admin.proposal.edit',
                    'delete' => 'admin.proposal.destroy',
                    'data'   => $row
                ]);
            })
            ->rawColumns(['status', 'action']);
    }

    public function query(Proposal $model): QueryBuilder
    {
        return $model->newQuery()->with('customer');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('proposal-table')
            ->addColumn(['data' => 'proposal_year', 'title' => __('Proposta'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'customer_name', 'title' => __('Cliente'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'total_value', 'title' => __('Valor'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'status', 'title' => __('Status'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'action', 'title' => __('Ações'), 'orderable' => false, 'searchable' => false])
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->parameters([
                'language' => [
                    'emptyTable' => 'Nenhuma proposta cadastrada',
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
        return 'Proposal_' . date('YmdHis');
    }
}
