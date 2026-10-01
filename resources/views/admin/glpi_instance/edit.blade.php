@extends('layouts.simple.master')

@section('title', 'Editar Instância GLPI')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Instâncias GLPI</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.glpi_instance.index') }}">Instâncias GLPI</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xxl-5 col-lg-5">
                <details>
                    <summary class="mb-2" style="cursor: pointer;">Precisa rever como configurar o GLPI?</summary>
                    @include('admin.glpi_instance.setup_guide')
                </details>
            </div>
            <div class="col-xxl-7 col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <form class="row g-3 custom-input" action="{{ route('admin.glpi_instance.update', $glpiInstance->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            @include('admin.glpi_instance.fields')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
