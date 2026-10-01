@extends('layouts.simple.master')

@section('title', 'Importar Nota Fiscal')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Importar Nota Fiscal</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.invoice.index') }}">Faturas</a></li>
                        <li class="breadcrumb-item active">Importar Nota Fiscal</li>
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
                        <div class="alert alert-light border" role="alert">
                            Suba o XML da NFS-e (o mesmo arquivo que o "Baixar XML" gera, ou o baixado direto do Emissor Nacional). O
                            sistema lê o CNPJ do tomador, competência e valor pra tentar casar com uma fatura já existente - se não achar,
                            cria uma nova (e o cliente, se também não existir), sempre com revisão antes de salvar.
                        </div>

                        <form class="row g-3 custom-input" action="{{ route('admin.nfse_import.analyze') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="col-sm-8">
                                <div class="mt-3">
                                    <label class="form-label">Arquivo XML<span> *</span></label>
                                    <input class="form-control" type="file" name="nfse_xml" accept=".xml" required>
                                    @error('nfse_xml')
                                        <span class="text-danger"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary">Analisar XML</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
