@extends('layouts.simple.master')

@section('title', 'Editar Proposta')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Propostas Comerciais</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.proposal.index') }}">Propostas Comerciais</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $proposal->title }}</h5>
                        <a href="{{ route('admin.proposal.preview', $proposal->id) }}" class="btn btn-outline-secondary" target="_blank">Visualizar Documento</a>
                    </div>
                    <div class="card-body">
                        <form class="row g-3 custom-input" action="{{ route('admin.proposal.update', $proposal->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            @include('admin.proposal.fields')
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Faturamento</h5>
                        <p class="text-muted mb-0">Sinal de Entrada e Parcela Final são geradas automaticamente ao salvar a proposta com status "Aceita".</p>
                    </div>
                    <div class="card-body">
                        @php
                            $proposalInvoices = $proposal->billingPolicies->flatMap->invoices;
                        @endphp
                        @if ($proposalInvoices->isEmpty())
                            @if ($proposal->status === \App\Models\Proposal::STATUS_ACCEPTED)
                                <p class="text-muted">Esta proposta está aceita mas ainda não tem faturas geradas.</p>
                                <form action="{{ route('admin.proposal.generate-invoices', $proposal->id) }}" method="POST"
                                    onsubmit="return confirm('Gerar as faturas de Entrada e Parcela Final agora, conforme os valores desta proposta?');">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Gerar Faturas</button>
                                </form>
                            @else
                                <p class="text-muted mb-0">Nenhuma fatura gerada ainda — marque a proposta como "Aceita" e salve.</p>
                            @endif
                        @else
                            <div class="table-responsive custom-scrollbar">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Descrição</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($proposal->billingPolicies as $policy)
                                            @foreach ($policy->invoices as $invoice)
                                                <tr>
                                                    <td>{{ $policy->description }}</td>
                                                    <td>R$ {{ number_format($invoice->amount, 2, ',', '.') }}</td>
                                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                                    <td>
                                                        @php
                                                            $invoiceStatusLabels = [
                                                                'pending' => '<span class="badge bg-warning">Pendente</span>',
                                                                'overdue' => '<span class="badge bg-danger">Vencida</span>',
                                                                'paid' => '<span class="badge bg-success">Paga</span>',
                                                                'cancelled' => '<span class="badge bg-secondary">Cancelada</span>',
                                                            ];
                                                        @endphp
                                                        {!! $invoiceStatusLabels[$invoice->status] ?? $invoice->status !!}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.invoice.edit', $invoice->id) }}" class="btn btn-sm btn-outline-secondary">Ver Fatura</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Versões</h5>
                        <p class="text-muted mb-0">Cada vez que a proposta é salva, uma nova versão em PDF é gerada e guardada no blob - o histórico completo fica disponível abaixo.</p>
                    </div>
                    <div class="card-body">
                        @if ($proposal->versions->isEmpty())
                            <p class="text-muted mb-0">Nenhuma versão gerada ainda.</p>
                        @else
                            <div class="table-responsive custom-scrollbar">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Versão</th>
                                            <th>Gerada em</th>
                                            <th>Por</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($proposal->versions as $version)
                                            <tr>
                                                <td>v{{ $version->version_number }}</td>
                                                <td>{{ $version->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $version->createdBy?->first_name }} {{ $version->createdBy?->last_name }}</td>
                                                <td>
                                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.proposal.versions.download', [$proposal->id, $version->id]) }}">Baixar PDF</a>
                                                    <form action="{{ route('admin.proposal.versions.destroy', [$proposal->id, $version->id]) }}" method="POST" class="d-inline"
                                                        onsubmit="return confirm('Excluir esta versão? O PDF será apagado do blob e não poderá ser recuperado.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('admin.proposal.scripts')
@endsection
