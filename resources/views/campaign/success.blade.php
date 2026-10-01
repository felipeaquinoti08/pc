@extends('layouts.authentication.master')

@section('title', 'Sua licença de teste')

@section('css')
@endsection

@section('main_content')
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card login-dark">
                    <div>
                        <div><a class="logo" href="{{ url('/') }}"><img class="img-fluid for-light"
                                    src="{{ asset('assets/images/logo/logo.png') }}" alt="logo"><img
                                    class="img-fluid for-dark" src="{{ asset('assets/images/logo/logo_dark.png') }}"
                                    alt="logo"></a></div>
                        <div class="login-main create-account">
                            <h4>Licença de teste liberada!</h4>
                            <p>{{ $campaign->name }} — válida até {{ $license->expires_at->format('d/m/Y') }}.</p>

                            <div class="alert alert-danger mt-3">
                                <strong>Guarde esta chave agora.</strong> Ainda não existe um painel do cliente para consultá-la depois — se você perder esta página e não salvar a chave, será necessário falar conosco pelo WhatsApp para recuperá-la.
                            </div>

                            <div class="form-group">
                                <label class="col-form-label pt-0">Chave da licença</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" id="license-key" value="{{ $license->license_key }}" readonly onclick="this.select()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('license-key').value)">Copiar</button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label">Instalações permitidas</label>
                                <p class="mb-0">{{ $license->quantity }}</p>
                            </div>

                            @if ($campaign->repository_url)
                                <div class="form-group">
                                    <label class="col-form-label">Onde baixar</label>
                                    <p><a href="{{ $campaign->repository_url }}" target="_blank" class="btn btn-primary">Acessar repositório</a></p>
                                </div>
                            @endif

                            @if ($campaign->instructions)
                                <div class="form-group">
                                    <label class="col-form-label">Instruções</label>
                                    <p>{!! nl2br(e($campaign->instructions)) !!}</p>
                                </div>
                            @endif

                            <p class="mt-4 mb-0 text-muted">
                                Precisa de ajuda para instalar ou ativar a licença? Fale conosco pelo WhatsApp:
                                <a href="{{ $campaign->whatsappLink() }}" target="_blank">{{ $campaign->whatsappNumber() }}</a>
                            </p>

                            <p class="mt-2 mb-0 text-muted">
                                <small>Salve o link desta página ou anote a chave acima — ela é o seu único acesso à licença.</small>
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
