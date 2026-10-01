<?php

namespace App\DataTables;

use App\Models\Campaign;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class CampaignDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('service_name', fn ($row) => $row->service?->name)
            ->addColumn('link', function ($row) {
                $url = route('campaign.show', $row->slug);
                return "<a href=\"{$url}\" target=\"_blank\">{$url}</a>";
            })
            ->addColumn('licenses_count', fn ($row) => $row->licenses()->count())
            ->editColumn('action', function ($row) {
                return view('admin.inc.action', [
                    'edit'   => 'admin.campaign.edit',
                    'delete' => 'admin.campaign.destroy',
                    'data'   => $row
                ]);
            })
            ->editColumn('status', function ($row) {
                return view('admin.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'admin.campaign.status',
                    'value' => $row->status,
                ]);
            })
            ->rawColumns(['link', 'action', 'status']);
    }

    public function query(Campaign $model): QueryBuilder
    {
        return $model->newQuery()->with('service');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('campaign-table')
            ->addColumn(['data' => 'name', 'title' => __('Nome'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'service_name', 'title' => __('Serviço'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'link', 'title' => __('Link público'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'licenses_count', 'title' => __('Licenças emitidas'), 'orderable' => false, 'searchable' => false])
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
        return 'Campaign_' . date('YmdHis');
    }
}
