@extends('layouts.authentication.master')

@section('title', $campaign->name)

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
                            @if (! $campaign->isAcceptingSignups())
                                <h4>{{ $campaign->name }}</h4>
                                <div class="alert alert-warning mt-3">
                                    Esta campanha de teste não está mais aceitando novos cadastros.
                                </div>
                                <p class="mt-3">
                                    Precisa de uma licença? Fale com a gente pelo WhatsApp:
                                    <a href="{{ $campaign->whatsappLink() }}" target="_blank">{{ $campaign->whatsappNumber() }}</a>
                                </p>
                            @else
                                <h4>{{ $campaign->name }}</h4>
                                @if ($campaign->description)
                                    <p>{!! nl2br(e($campaign->description)) !!}</p>
                                @else
                                    <p>Preencha os dados abaixo para receber uma licença de teste gratuita.</p>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <form class="theme-form" method="POST" action="{{ route('campaign.store', $campaign->slug) }}">
                                    @csrf

                                    {{-- Honeypot: hidden from real visitors via CSS, bots fill every field --}}
                                    <div style="position:absolute; left:-9999px;" aria-hidden="true">
                                        <label for="website">Deixe em branco</label>
                                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label class="col-form-label pt-0">Nome da empresa ou responsável<span class="text-danger"> *</span></label>
                                        <input class="form-control" type="text" name="name" value="{{ old('name') }}" required placeholder="Sua empresa ou seu nome">
                                        @error('name')
                                            <span class="text-danger"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="col-form-label">E-mail<span class="text-danger"> *</span></label>
                                        <input class="form-control" type="email" name="email" value="{{ old('email') }}" required placeholder="voce@empresa.com.br">
                                        @error('email')
                                            <span class="text-danger"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="col-form-label">WhatsApp / Telefone<span class="text-danger"> *</span></label>
                                        <input class="form-control" type="text" name="phone" value="{{ old('phone') }}" required placeholder="(00) 00000-0000">
                                        @error('phone')
                                            <span class="text-danger"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="col-form-label">CPF/CNPJ (opcional)</label>
                                        <div class="row g-2">
                                            <div class="col-sm-4">
                                                <select class="form-control" name="document_type">
                                                    <option value="">Tipo</option>
                                                    <option value="cpf" {{ old('document_type') == 'cpf' ? 'selected' : '' }}>CPF</option>
                                                    <option value="cnpj" {{ old('document_type') == 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-8">
                                                <input class="form-control" type="text" name="document_number" value="{{ old('document_number') }}" placeholder="Número do documento">
                                            </div>
                                        </div>
                                        @error('document_type')
                                            <span class="text-danger"><strong>{{ $message }}</strong></span>
                                        @enderror
                                        @error('document_number')
                                            <span class="text-danger"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-0">
                                        <button class="btn btn-primary btn-block w-100 mt-3" type="submit">Quero testar grátis</button>
                                    </div>
                                </form>

                                <p class="mt-4 mb-0 text-muted">
                                    Dúvidas? Fale conosco pelo WhatsApp:
                                    <a href="{{ $campaign->whatsappLink() }}" target="_blank">{{ $campaign->whatsappNumber() }}</a>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
