@extends('layouts.simple.master')

@section('title', 'Prévia da Importação (GLPI)')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Prévia da Importação</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.glpi_asset_import.create') }}">Importar Ativos (GLPI)</a></li>
                        <li class="breadcrumb-item active">Prévia</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Destino: {{ $instance->name }} ({{ $instance->base_url }})</h5>
                        <p class="mb-0">Nada foi gravado no GLPI ainda. Confira os números abaixo e confirme no final da página.</p>
                    </div>
                    <div class="card-body">
                        <h6>Ativos por tipo ({{ $stats['total_rows'] }} linha(s) válida(s))</h6>
                        <table class="table table-sm w-auto mb-4">
                            <tbody>
                                @foreach ($stats['by_type'] as $itemtype => $count)
                                    <tr>
                                        <td>{{ $itemtype }}</td>
                                        <td><strong>{{ $count }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <h6>Cadastros de apoio (Localização, Fabricante, Status, Tipo, Modelo)</h6>
                        <table class="table table-sm mb-4">
                            <thead>
                                <tr>
                                    <th>Dropdown do GLPI</th>
                                    <th>Já existe</th>
                                    <th>Será criado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stats['dropdowns'] as $dropdown => $info)
                                    <tr>
                                        <td>{{ $dropdown }}</td>
                                        <td><span class="badge bg-secondary">{{ $info['existing'] }}</span></td>
                                        <td><span class="badge bg-success">{{ $info['new'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <h6>Termo de entrega assinado (Assinei)</h6>
                        <p>{{ $stats['signed_terms'] }} ativo(s) marcados como assinados no CSV ({{ $stats['with_link'] }} com link registrado). Isso vira uma anotação no campo "Comentário" de cada ativo.</p>

                        @if (! empty($parseErrors))
                            <div class="alert alert-warning">
                                <strong>{{ count($parseErrors) }} linha(s) do CSV ignorada(s):</strong>
                                <ul class="mb-0">
                                    @foreach ($parseErrors as $line => $reason)
                                        <li>Linha {{ $line }}: {{ $reason }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.glpi_asset_import.store') }}" method="POST"
                            onsubmit="return confirm('Confirmar a importação de {{ $stats['total_rows'] }} ativo(s) pra {{ $instance->name }}? Essa ação grava de verdade no GLPI.');">
                            @csrf
                            <input type="hidden" name="glpi_instance_id" value="{{ $instance->id }}">
                            <input type="hidden" name="token" value="{{ $token }}">
                            <button type="submit" class="btn btn-primary">Confirmar Importação</button>
                            <a href="{{ route('admin.glpi_asset_import.create') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
