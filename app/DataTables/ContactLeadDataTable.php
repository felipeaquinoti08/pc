<?php

namespace App\DataTables;

use App\Models\ContactLead;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ContactLeadDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('created_at', function ($row) {
                return $row->created_at->diffForHumans();
            })
            ->editColumn('name', function ($row) {
                return view('admin.contact_lead.partials.name-column', ['row' => $row])->render();
            })
            ->editColumn('customer_id', function ($row) {
                return $row->customer
                    ? '<a href="' . route('admin.customer.edit', $row->customer_id) . '">' . e($row->customer->name) . '</a>'
                    : '-';
            })
            ->editColumn('status', function ($row) {
                return view('admin.contact_lead.partials.status-select', ['row' => $row])->render();
            })
            ->editColumn('action', function ($row) {
                return view('admin.inc.action', [
                    'delete' => 'admin.contact_lead.destroy',
                    'data'   => $row,
                ]);
            })
            ->rawColumns(['created_at', 'name', 'customer_id', 'status', 'action']);
    }

    public function query(ContactLead $model): QueryBuilder
    {
        return $model->newQuery()->with('customer')->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('contact-lead-table')
            ->addColumn(['data' => 'name', 'title' => __('Nome'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'email', 'title' => __('E-mail'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'phone', 'title' => __('Telefone'), 'orderable' => false, 'searchable' => true])
            ->addColumn(['data' => 'company', 'title' => __('Empresa'), 'orderable' => false, 'searchable' => true])
            ->addColumn(['data' => 'customer_id', 'title' => __('Cliente'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'status', 'title' => __('Status'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'created_at', 'title' => __('Recebido'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'action', 'title' => __('Ações'), 'orderable' => false, 'searchable' => false])
            ->minifiedAjax()
            ->orderBy(6, 'desc')
            ->selectStyleSingle()
            ->parameters([
                'language' => [
                    'emptyTable' => 'Nenhum contato recebido ainda',
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
        return 'ContactLead_' . date('YmdHis');
    }
}
