@extends('layouts.simple.master')

@section('title', 'Editar Licenciamento')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Licenciamento</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.license.index') }}">Licenciamento</a></li>
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
                    <div class="card-body">
                        <form class="row g-3 custom-input" action="{{ route('admin.license.update', $license->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            @include('admin.license.fields')
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Instalações ativadas ({{ $license->activeActivations()->count() }}/{{ $license->quantity }})</h5>
                        <p class="text-muted mb-0">Cada instalação do plugin que valida esta chave conta uma vaga. Vagas só são liberadas manualmente.</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive custom-scrollbar">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Instância</th>
                                        <th>URL</th>
                                        <th>Ativada em</th>
                                        <th>Último check-in</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($license->activations as $activation)
                                        <tr>
                                            <td><code>{{ $activation->instance_identifier }}</code></td>
                                            <td>{{ $activation->instance_url ?? '-' }}</td>
                                            <td>{{ $activation->activated_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $activation->last_seen_at->diffForHumans() }}</td>
                                            <td>
                                                @if ($activation->status === 'active')
                                                    <span class="badge bg-success">Ativa</span>
                                                @else
                                                    <span class="badge bg-secondary">Revogada</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($activation->status === 'active')
                                                    <form action="{{ route('admin.license.activations.revoke', [$license->id, $activation->id]) }}" method="POST" onsubmit="return confirm('Revogar esta instalação? A vaga fica livre para outra instalação usar.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Revogar</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Nenhuma instalação validou esta chave ainda.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
