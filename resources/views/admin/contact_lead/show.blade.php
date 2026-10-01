@extends('layouts.simple.master')

@section('title', 'Contato do Site')

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Contato do Site</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.contact_lead.index') }}">Contatos do Site</a></li>
                        <li class="breadcrumb-item active">{{ $contactLead->name }}</li>
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
                        <h5 class="mb-0">{{ $contactLead->name }}</h5>
                        <div class="d-flex align-items-center gap-2">
                            @include('admin.contact_lead.partials.status-select', ['row' => $contactLead])
                            @if ($contactLead->customer)
                                <a href="{{ route('admin.customer.edit', $contactLead->customer_id) }}" class="btn btn-primary btn-sm">
                                    Ver Cliente
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>E-mail</strong><p>{{ $contactLead->email }}</p></div>
                            <div class="col-md-3"><strong>Telefone</strong><p>{{ $contactLead->phone ?: '-' }}</p></div>
                            <div class="col-md-3"><strong>Empresa</strong><p>{{ $contactLead->company ?: '-' }}</p></div>
                            <div class="col-md-3"><strong>Recebido em</strong><p>{{ $contactLead->created_at->format('d/m/Y H:i') }}</p></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Origem</strong><p>{{ $contactLead->source ?: '-' }}</p></div>
                            <div class="col-md-3"><strong>IP</strong><p>{{ $contactLead->ip_address ?: '-' }}</p></div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <strong>Mensagem</strong>
                                <p class="mt-1" style="white-space: pre-wrap;">{{ $contactLead->message ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('admin.contact_lead.partials.status-script')
@endsection
