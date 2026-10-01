@extends('layouts.simple.master')

@section('title', 'Importação (GLPI)')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Importação de Ativos</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item active">Importação #{{ $import->id }}</li>
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
                        <h5 class="mb-0">Destino: {{ $import->glpiInstance->name }} ({{ $import->glpiInstance->base_url }})</h5>
                    </div>
                    <div class="card-body">
                        @if (! $import->isFinished())
                            <div class="alert alert-info d-flex align-items-center mb-0">
                                <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                                <div>
                                    @if ($import->status === \App\Models\GlpiAssetImport::STATUS_PROCESSING)
                                        Importando {{ $import->total_rows }} linha(s) em segundo plano... Esta página atualiza sozinha.
                                    @else
                                        Na fila para processamento em segundo plano ({{ $import->total_rows }} linha(s))... Esta página atualiza sozinha.
                                    @endif
                                </div>
                            </div>
                        @elseif ($import->status === \App\Models\GlpiAssetImport::STATUS_FAILED)
                            <div class="alert alert-danger mb-0">
                                Falha ao importar: {{ $import->failure_reason }}
                            </div>
                        @else
                            <div class="row g-3 mb-3">
                                <div class="col-sm-4">
                                    <div class="alert alert-success mb-0">
                                        <strong>{{ $import->created_count }}</strong> ativo(s) criado(s)
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="alert alert-info mb-0">
                                        <strong>{{ $import->updated_count }}</strong> ativo(s) atualizado(s) (já existiam)
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="alert {{ count($import->errors ?? []) > 0 ? 'alert-danger' : 'alert-secondary' }} mb-0">
                                        <strong>{{ count($import->errors ?? []) }}</strong> linha(s) com erro
                                    </div>
                                </div>
                            </div>

                            @if (! empty($import->items))
                                @php
                                    // Convenção de URL de formulário do GLPI (front/{itemtype em
                                    // minúsculo}.form.php?id=) - vale pros itemtypes que este
                                    // módulo usa hoje (Computer, Monitor, Phone,
                                    // NetworkEquipment, Line).
                                    $glpiFormUrl = fn ($itemtype, $id) => rtrim($import->glpiInstance->base_url, '/')
                                        . '/front/' . strtolower($itemtype) . '.form.php?id=' . $id;
                                @endphp
                                <h6>Itens criados/atualizados ({{ count($import->items) }})</h6>
                                <div class="table-responsive custom-scrollbar mb-4" style="max-height: 400px;">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Linha do CSV</th>
                                                <th>Tipo</th>
                                                <th>Nome</th>
                                                <th>Ação</th>
                                                <th>GLPI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($import->items as $item)
                                                <tr>
                                                    <td>{{ $item['line'] }}</td>
                                                    <td>{{ $item['itemtype'] }}</td>
                                                    <td>{{ $item['nome'] }}</td>
                                                    <td>
                                                        @if ($item['action'] === 'created')
                                                            <span class="badge bg-success">Criado</span>
                                                        @else
                                                            <span class="badge bg-info">Atualizado</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($item['glpi_id'])
                                                            <a href="{{ $glpiFormUrl($item['itemtype'], $item['glpi_id']) }}" target="_blank" rel="noopener">Abrir #{{ $item['glpi_id'] }}</a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if (! empty($import->errors))
                                <h6>Detalhe dos erros</h6>
                                <div class="table-responsive custom-scrollbar">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Linha do CSV</th>
                                                <th>Erro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($import->errors as $line => $reason)
                                                <tr>
                                                    <td>{{ $line }}</td>
                                                    <td>{{ $reason }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif

                        <a href="{{ route('admin.glpi_asset_import.create') }}" class="btn btn-primary mt-3">Nova Importação</a>
                        <a href="{{ route('admin.glpi_asset_import.index') }}" class="btn btn-outline-secondary mt-3">Ver Todas as Importações</a>

                        @if ($import->isFinished())
                            <form action="{{ route('admin.glpi_asset_import.destroy', $import) }}" method="post" class="d-inline"
                                onsubmit="return confirm('Remover este registro de importação? Isso não desfaz o que já foi criado/atualizado no GLPI, só some do histórico daqui.');">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-outline-danger mt-3">Remover Registro</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if (! $import->isFinished())
        <script>
            setTimeout(function () {
                window.location.reload();
            }, 5000);
        </script>
    @endif
@endsection
