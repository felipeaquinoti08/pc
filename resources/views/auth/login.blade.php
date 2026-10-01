@extends('layouts.authentication.master')

@section('title', 'Login')

@section('css')
@endsection

@section('main_content')
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card login-dark">
                    <div>
                        <div><a class="logo" href="{{ url('/') }}"><img class="img-fluid for-light"
                                    src="{{ asset('assets/images/logo/logo.svg') }}" alt="looginpage"><img class="img-fluid for-dark"
                                    src="{{ asset('assets/images/logo/logo_dark.svg') }}" alt="looginpage"></a></div>
                        <div class="login-main">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <h4>Entrar</h4>
                            <p>Use sua conta Microsoft da empresa para acessar o painel.</p>

                            <a href="{{ route('auth.microsoft.redirect') }}" class="btn btn-primary btn-block w-100 mt-3 d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-brands fa-microsoft"></i> Entrar com Microsoft
                            </a>

                            <p class="mt-4 mb-0 text-center">
                                <small class="text-muted">Prefere entrar com usuário e senha?
                                    <a href="{{ route('login.local') }}">Clique aqui</a>.</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
