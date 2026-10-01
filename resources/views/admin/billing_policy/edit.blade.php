@extends('layouts.simple.master')

@section('title', 'Editar Política de Faturamento')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Políticas de Faturamento</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.billing_policy.index') }}">Políticas de Faturamento</a></li>
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
                        <div>
                            <h5 class="mb-0">Faturas desta política</h5>
                            <p class="text-muted mb-0">Confere se todas as competências devidas desde o início da política já foram faturadas e gera as que faltarem.</p>
                        </div>
                        <form action="{{ route('admin.billing_policy.reconcile', $policy->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">{{ __('Validar Faturas') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form class="row g-3 custom-input" action="{{ route('admin.billing_policy.update', $policy->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            @include('admin.billing_policy.fields')
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($policy->invoices()->exists())
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Faturas geradas por esta política</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive custom-scrollbar">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Competência</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($policy->invoices()->latest('due_date')->limit(12)->get() as $invoice)
                                            <tr>
                                                <td>{{ sprintf('%02d/%d', $invoice->reference_month, $invoice->reference_year) }}</td>
                                                <td>R$ {{ number_format($invoice->amount, 2, ',', '.') }}</td>
                                                <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                                <td>
                                                    @php
                                                        $labels = [
                                                            'pending' => '<span class="badge bg-secondary">Pendente</span>',
                                                            'overdue' => '<span class="badge bg-warning">Atrasada</span>',
                                                            'paid' => '<span class="badge bg-success">Paga</span>',
                                                            'cancelled' => '<span class="badge bg-danger">Cancelada</span>',
                                                        ];
                                                    @endphp
                                                    {!! $labels[$invoice->status] ?? $invoice->status !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    @include('admin.billing_policy.scripts')
@endsection
