@extends('layouts.simple.master')

@section('title', 'Configurações')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Configurações</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item active">Configurações</li>
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
                        <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="sso-tab" data-bs-toggle="tab"
                                    href="#sso" role="tab" aria-controls="sso" aria-selected="true">SSO (Microsoft Entra ID)</a></li>
                            <li class="nav-item"><a class="nav-link" id="storage-tab" data-bs-toggle="tab"
                                    href="#storage" role="tab" aria-controls="storage" aria-selected="false">Armazenamento (Azure Blob)</a></li>
                            <li class="nav-item"><a class="nav-link" id="mercadopago-tab" data-bs-toggle="tab"
                                    href="#mercadopago" role="tab" aria-controls="mercadopago" aria-selected="false">Pagamentos (Mercado Pago)</a></li>
                            <li class="nav-item"><a class="nav-link" id="mail-tab" data-bs-toggle="tab"
                                    href="#mail" role="tab" aria-controls="mail" aria-selected="false">E-mail (SMTP)</a></li>
                            <li class="nav-item"><a class="nav-link" id="nfse-tab" data-bs-toggle="tab"
                                    href="#nfse" role="tab" aria-controls="nfse" aria-selected="false">Nota Fiscal (NFS-e)</a></li>
                        </ul>
                        <div class="tab-content pt-4" id="settingsTabContent">
                            {{-- SSO tab --}}
                            <div class="tab-pane fade show active" id="sso" role="tabpanel" aria-labelledby="sso-tab">
                                <form class="row g-3 custom-input" action="{{ route('admin.setting.sso.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="col-12">
                                        <div class="alert alert-light border" role="alert">
                                            Cadastre um <strong>App registration</strong> no Azure Portal (conta de organização única, plataforma
                                            "Web") com esta URL de redirecionamento e cole aqui o Tenant ID, Client ID e o Client Secret gerado.
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="mt-3">
                                            <label class="form-label">URL de Redirecionamento (Redirect URI)</label>
                                            <input class="form-control" type="text" value="{{ route('auth.microsoft.callback') }}" readonly onclick="this.select()">
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Tenant ID<span> *</span></label>
                                            <input class="form-control" type="text" name="sso_tenant_id"
                                                value="{{ old('sso_tenant_id', $setting->sso_tenant_id) }}">
                                            @error('sso_tenant_id')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Client ID (Application ID)<span> *</span></label>
                                            <input class="form-control" type="text" name="sso_client_id"
                                                value="{{ old('sso_client_id', $setting->sso_client_id) }}">
                                            @error('sso_client_id')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Client Secret</label>
                                            <input class="form-control" type="password" name="sso_client_secret"
                                                placeholder="{{ $setting->sso_client_secret ? 'Preenchido - deixe em branco para manter' : 'Nenhum salvo ainda' }}"
                                                autocomplete="new-password">
                                            @error('sso_client_secret')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label d-block">Status</label>
                                            @if ($setting->ssoConfigured())
                                                <span class="badge bg-success">Configurado</span>
                                            @else
                                                <span class="badge bg-secondary">Não configurado</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary">{{ __('Salvar SSO') }}</button>
                                    </div>
                                </form>
                            </div>

                            {{-- Storage tab --}}
                            <div class="tab-pane fade" id="storage" role="tabpanel" aria-labelledby="storage-tab">
                                <form class="row g-3 custom-input" id="storageForm" action="{{ route('admin.setting.storage.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="col-12">
                                        <div class="alert alert-light border" role="alert">
                                            Crie uma <strong>Storage Account</strong> e um <strong>Blob container</strong> (acesso privado) no Azure Portal
                                            e cole aqui o nome da conta, uma das chaves de acesso e o nome do container. Nenhum arquivo enviado pelo painel
                                            é gravado localmente - o envio fica bloqueado até isso estar configurado.
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Storage Account Name<span> *</span></label>
                                            <input class="form-control" type="text" id="blob_account_name" name="blob_account_name"
                                                value="{{ old('blob_account_name', $setting->blob_account_name) }}">
                                            @error('blob_account_name')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Container<span> *</span></label>
                                            <input class="form-control" type="text" id="blob_container" name="blob_container"
                                                value="{{ old('blob_container', $setting->blob_container) }}">
                                            @error('blob_container')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Account Key</label>
                                            <input class="form-control" type="password" id="blob_account_key" name="blob_account_key"
                                                placeholder="{{ $setting->blob_account_key ? 'Preenchida - deixe em branco para manter' : 'Nenhuma salva ainda' }}"
                                                autocomplete="new-password">
                                            @error('blob_account_key')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label d-block">Status</label>
                                            @if ($setting->blobConfigured())
                                                <span class="badge bg-success">Configurado</span>
                                            @else
                                                <span class="badge bg-secondary">Não configurado</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <a class="d-inline-flex align-items-center gap-1" data-bs-toggle="collapse" href="#storageAdvanced" role="button">
                                            Avançado
                                        </a>
                                        <div class="collapse {{ old('blob_endpoint_suffix') ? 'show' : '' }}" id="storageAdvanced">
                                            <div class="mt-3">
                                                <label class="form-label">Endpoint Suffix</label>
                                                <input class="form-control" type="text" id="blob_endpoint_suffix" name="blob_endpoint_suffix"
                                                    value="{{ old('blob_endpoint_suffix', $setting->blob_endpoint_suffix) }}" placeholder="core.windows.net">
                                                <small class="text-muted">Só precisa mudar em nuvens soberanas (ex: Azure China, Azure Government).</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">{{ __('Salvar Armazenamento') }}</button>
                                        <button type="button" class="btn btn-outline-secondary" id="testStorageBtn">Testar conexão</button>
                                        <small class="text-muted align-self-center" id="testStorageResult"></small>
                                    </div>
                                </form>
                            </div>

                            {{-- Mercado Pago tab --}}
                            <div class="tab-pane fade" id="mercadopago" role="tabpanel" aria-labelledby="mercadopago-tab">
                                <form class="row g-3 custom-input" action="{{ route('admin.setting.mercadopago.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="col-12">
                                        <div class="alert alert-light border" role="alert">
                                            Crie uma aplicação no <strong>Mercado Pago Developers</strong> e cole aqui as credenciais de <strong>Teste</strong>
                                            e de <strong>Produção</strong> (cada uma tem seu próprio Webhook Secret - a Mercado Pago mostra os dois separadamente
                                            em Suas integrações → Webhooks). Notificações chegam de qualquer um dos dois modos e são reconhecidas automaticamente
                                            - o "Ambiente ativo" só decide com qual credencial os <strong>novos</strong> links de pagamento são gerados.
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="mt-3">
                                            <label class="form-label">URL de Notificação (Webhook)</label>
                                            <input class="form-control" type="text" value="{{ route('api.mercadopago.webhook') }}" readonly onclick="this.select()">
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label d-block">Ambiente ativo (para novos links de pagamento)</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="mp_environment" id="mp_env_test" value="test"
                                                    {{ old('mp_environment', $setting->mp_environment ?? 'test') == 'test' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mp_env_test">Teste</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="mp_environment" id="mp_env_prod" value="production"
                                                    {{ old('mp_environment', $setting->mp_environment ?? 'test') == 'production' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mp_env_prod">Produção</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label d-block">Status</label>
                                            @if ($setting->mercadoPagoConfigured())
                                                <span class="badge bg-success">Configurado ({{ $setting->mercadoPagoIsTestEnvironment() ? 'Teste' : 'Produção' }})</span>
                                            @else
                                                <span class="badge bg-secondary">Não configurado</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3"><h6 class="mb-0">Credenciais de Teste</h6></div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Public Key (teste)</label>
                                            <input class="form-control" type="text" name="mp_test_public_key"
                                                value="{{ old('mp_test_public_key', $setting->mp_test_public_key) }}">
                                            @error('mp_test_public_key')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Access Token (teste)</label>
                                            <input class="form-control" type="password" name="mp_test_access_token"
                                                placeholder="{{ $setting->mp_test_access_token ? 'Preenchido - deixe em branco para manter' : 'Nenhum salvo ainda' }}"
                                                autocomplete="new-password">
                                            @error('mp_test_access_token')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Webhook Secret (teste)</label>
                                            <input class="form-control" type="password" name="mp_test_webhook_secret"
                                                placeholder="{{ $setting->mp_test_webhook_secret ? 'Preenchido - deixe em branco para manter' : 'Nenhum salvo ainda' }}"
                                                autocomplete="new-password">
                                            @error('mp_test_webhook_secret')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3"><h6 class="mb-0">Credenciais de Produção</h6></div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Public Key (produção)</label>
                                            <input class="form-control" type="text" name="mp_public_key"
                                                value="{{ old('mp_public_key', $setting->mp_public_key) }}">
                                            @error('mp_public_key')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Access Token (produção)</label>
                                            <input class="form-control" type="password" name="mp_access_token"
                                                placeholder="{{ $setting->mp_access_token ? 'Preenchido - deixe em branco para manter' : 'Nenhum salvo ainda' }}"
                                                autocomplete="new-password">
                                            @error('mp_access_token')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Webhook Secret (produção)</label>
                                            <input class="form-control" type="password" name="mp_webhook_secret"
                                                placeholder="{{ $setting->mp_webhook_secret ? 'Preenchido - deixe em branco para manter' : 'Nenhum salvo ainda' }}"
                                                autocomplete="new-password">
                                            @error('mp_webhook_secret')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary">{{ __('Salvar Mercado Pago') }}</button>
                                    </div>
                                </form>
                            </div>

                            {{-- Mail tab --}}
                            <div class="tab-pane fade" id="mail" role="tabpanel" aria-labelledby="mail-tab">
                                <form class="row g-3 custom-input" id="mailForm" action="{{ route('admin.setting.mail.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="col-12">
                                        <div class="alert alert-light border" role="alert">
                                            Dados do servidor SMTP usado para enviar as notificações de faturas (criada, lembrete de vencimento,
                                            pagamento confirmado, licença suspensa).
                                        </div>
                                    </div>

                                    <div class="col-sm-8">
                                        <div class="mt-3">
                                            <label class="form-label">Host<span> *</span></label>
                                            <input class="form-control" type="text" id="mail_host" name="mail_host"
                                                value="{{ old('mail_host', $setting->mail_host) }}" placeholder="smtp.exemplo.com.br">
                                            @error('mail_host')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Porta<span> *</span></label>
                                            <input class="form-control" type="text" id="mail_port" name="mail_port"
                                                value="{{ old('mail_port', $setting->mail_port) }}" placeholder="587">
                                            @error('mail_port')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Usuário</label>
                                            <input class="form-control" type="text" id="mail_username" name="mail_username"
                                                value="{{ old('mail_username', $setting->mail_username) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Senha</label>
                                            <input class="form-control" type="password" id="mail_password" name="mail_password"
                                                placeholder="{{ $setting->mail_password ? 'Preenchida - deixe em branco para manter' : 'Nenhuma salva ainda' }}"
                                                autocomplete="new-password">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Criptografia</label>
                                            <select class="form-select" id="mail_encryption" name="mail_encryption">
                                                <option value="" {{ old('mail_encryption', $setting->mail_encryption) == '' ? 'selected' : '' }}>Nenhuma</option>
                                                <option value="tls" {{ old('mail_encryption', $setting->mail_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                                                <option value="ssl" {{ old('mail_encryption', $setting->mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">E-mail do remetente<span> *</span></label>
                                            <input class="form-control" type="email" id="mail_from_address" name="mail_from_address"
                                                value="{{ old('mail_from_address', $setting->mail_from_address) }}">
                                            @error('mail_from_address')
                                                <span class="text-danger"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Nome do remetente</label>
                                            <input class="form-control" type="text" id="mail_from_name" name="mail_from_name"
                                                value="{{ old('mail_from_name', $setting->mail_from_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label d-block">Status</label>
                                            @if ($setting->mailConfigured())
                                                <span class="badge bg-success">Configurado</span>
                                            @else
                                                <span class="badge bg-secondary">Não configurado</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3 d-flex gap-2 align-items-center flex-wrap">
                                        <button type="submit" class="btn btn-primary">{{ __('Salvar E-mail') }}</button>
                                        <input class="form-control" type="email" id="test_mail_to" placeholder="e-mail para receber o teste" style="max-width: 260px;">
                                        <button type="button" class="btn btn-outline-secondary" id="testMailBtn">Enviar e-mail de teste</button>
                                        <small class="text-muted" id="testMailResult"></small>
                                    </div>
                                </form>
                            </div>

                            {{-- NFS-e tab --}}
                            <div class="tab-pane fade" id="nfse" role="tabpanel" aria-labelledby="nfse-tab">
                                <form class="row g-3 custom-input" id="nfseForm" action="{{ route('admin.setting.nfse.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="col-12">
                                        <div class="alert alert-warning border" role="alert">
                                            Integração direta com o Ambiente Nacional de NFS-e (nfse.gov.br). O envio autentica com o
                                            <strong>certificado digital A1 (.pfx)</strong> do CNPJ prestador - o arquivo fica guardado no Azure Blob
                                            (nunca em disco local), e a senha fica criptografada no banco. Emita sempre em <strong>Homologação</strong>
                                            primeiro e confira o resultado antes de trocar para Produção.
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label d-block">Ambiente</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="nfse_environment" id="nfse_env_homolog" value="homologacao"
                                                    {{ old('nfse_environment', $setting->nfse_environment ?? 'homologacao') == 'homologacao' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="nfse_env_homolog">Homologação (testes)</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="nfse_environment" id="nfse_env_prod" value="producao"
                                                    {{ old('nfse_environment', $setting->nfse_environment ?? 'homologacao') == 'producao' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="nfse_env_prod">Produção</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label d-block">Status</label>
                                            @if ($setting->nfseConfigured())
                                                <span class="badge bg-success">Configurado ({{ $setting->nfseIsProduction() ? 'Produção' : 'Homologação' }})</span>
                                            @else
                                                <span class="badge bg-secondary">Não configurado</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3"><h6 class="mb-0">Certificado digital</h6></div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Certificado (.pfx)</label>
                                            <input class="form-control" type="file" id="nfse_certificate" name="nfse_certificate" accept=".pfx,.p12">
                                            <small class="text-muted">{{ $setting->nfse_certificate_path ? 'Já existe um certificado enviado - escolha um arquivo só para substituir.' : 'Nenhum certificado enviado ainda.' }}</small>
                                            @error('nfse_certificate')
                                                <span class="text-danger d-block"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-3">
                                            <label class="form-label">Senha do certificado</label>
                                            <input class="form-control" type="password" id="nfse_certificate_password" name="nfse_certificate_password"
                                                placeholder="{{ $setting->nfse_certificate_password ? 'Preenchida - deixe em branco para manter' : 'Nenhuma salva ainda' }}"
                                                autocomplete="new-password">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="testNfseCertBtn">Testar certificado</button>
                                        <small class="text-muted" id="testNfseCertResult"></small>
                                    </div>

                                    <div class="col-12 mt-3"><h6 class="mb-0">Prestador</h6></div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">CNPJ<span> *</span></label>
                                            <input class="form-control" type="text" name="nfse_cnpj"
                                                value="{{ old('nfse_cnpj', $setting->nfse_cnpj) }}" placeholder="00000000000000">
                                            @error('nfse_cnpj')<span class="text-danger"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Inscrição Municipal</label>
                                            <input class="form-control" type="text" name="nfse_inscricao_municipal"
                                                value="{{ old('nfse_inscricao_municipal', $setting->nfse_inscricao_municipal) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Regime (opSimpNac)<span> *</span></label>
                                            <select class="form-select" name="nfse_op_simples_nacional">
                                                <option value="1" {{ old('nfse_op_simples_nacional', $setting->nfse_op_simples_nacional) == 1 ? 'selected' : '' }}>1 - Simples Nacional</option>
                                                <option value="2" {{ old('nfse_op_simples_nacional', $setting->nfse_op_simples_nacional) == 2 ? 'selected' : '' }}>2 - Simples Nacional (excesso)</option>
                                                <option value="3" {{ old('nfse_op_simples_nacional', $setting->nfse_op_simples_nacional) == 3 ? 'selected' : '' }}>3 - Não optante</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Regime Especial (regEspTrib)<span> *</span></label>
                                            <input class="form-control" type="number" min="0" max="6" name="nfse_reg_esp_trib"
                                                value="{{ old('nfse_reg_esp_trib', $setting->nfse_reg_esp_trib ?? 0) }}">
                                            <small class="text-muted">0 = Nenhum</small>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Telefone</label>
                                            <input class="form-control" type="text" name="nfse_telefone"
                                                value="{{ old('nfse_telefone', $setting->nfse_telefone) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">E-mail</label>
                                            <input class="form-control" type="email" name="nfse_email"
                                                value="{{ old('nfse_email', $setting->nfse_email) }}">
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3"><h6 class="mb-0">Endereço / Município de emissão</h6></div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Código do Município (IBGE)<span> *</span></label>
                                            <input class="form-control" type="text" name="nfse_municipio_codigo"
                                                value="{{ old('nfse_municipio_codigo', $setting->nfse_municipio_codigo) }}" placeholder="3304557">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Município<span> *</span></label>
                                            <input class="form-control" type="text" name="nfse_municipio_nome"
                                                value="{{ old('nfse_municipio_nome', $setting->nfse_municipio_nome) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="mt-3">
                                            <label class="form-label">UF<span> *</span></label>
                                            <input class="form-control" type="text" name="nfse_uf" maxlength="2"
                                                value="{{ old('nfse_uf', $setting->nfse_uf) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">CEP</label>
                                            <input class="form-control" type="text" name="nfse_cep"
                                                value="{{ old('nfse_cep', $setting->nfse_cep) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="mt-3">
                                            <label class="form-label">Logradouro</label>
                                            <input class="form-control" type="text" name="nfse_logradouro"
                                                value="{{ old('nfse_logradouro', $setting->nfse_logradouro) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="mt-3">
                                            <label class="form-label">Número</label>
                                            <input class="form-control" type="text" name="nfse_numero"
                                                value="{{ old('nfse_numero', $setting->nfse_numero) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="mt-3">
                                            <label class="form-label">Bairro</label>
                                            <input class="form-control" type="text" name="nfse_bairro"
                                                value="{{ old('nfse_bairro', $setting->nfse_bairro) }}">
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3"><h6 class="mb-0">Serviço e alíquotas</h6></div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Série da DPS<span> *</span></label>
                                            <input class="form-control" type="text" name="nfse_dps_serie"
                                                value="{{ old('nfse_dps_serie', $setting->nfse_dps_serie ?? '1') }}">
                                            <small class="text-muted">1–49999 (aplicativo próprio/API). 70000–79999 é reservado ao Emissor Web do governo.</small>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Cód. Tributação Nacional<span> *</span></label>
                                            <input class="form-control" type="text" name="nfse_codigo_tributacao_nacional"
                                                value="{{ old('nfse_codigo_tributacao_nacional', $setting->nfse_codigo_tributacao_nacional) }}" placeholder="010701">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Cód. Tributação Municipal</label>
                                            <input class="form-control" type="text" name="nfse_codigo_tributacao_municipal"
                                                value="{{ old('nfse_codigo_tributacao_municipal', $setting->nfse_codigo_tributacao_municipal) }}" placeholder="001">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="mt-3">
                                            <label class="form-label">Código NBS</label>
                                            <input class="form-control" type="text" name="nfse_codigo_nbs"
                                                value="{{ old('nfse_codigo_nbs', $setting->nfse_codigo_nbs) }}" placeholder="115013000">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Alíquota ISS (%)<span> *</span></label>
                                            <input class="form-control" type="number" step="0.01" min="0" max="100" name="nfse_aliquota_iss"
                                                value="{{ old('nfse_aliquota_iss', $setting->nfse_aliquota_iss) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Alíquota PIS (%)</label>
                                            <input class="form-control" type="number" step="0.01" min="0" max="100" name="nfse_aliquota_pis"
                                                value="{{ old('nfse_aliquota_pis', $setting->nfse_aliquota_pis) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">Alíquota COFINS (%)</label>
                                            <input class="form-control" type="number" step="0.01" min="0" max="100" name="nfse_aliquota_cofins"
                                                value="{{ old('nfse_aliquota_cofins', $setting->nfse_aliquota_cofins) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mt-3">
                                            <label class="form-label">% Aprox. Tributos Federais<span> *</span></label>
                                            <input class="form-control" type="number" step="0.01" min="0" max="100" name="nfse_percentual_trib_federal"
                                                value="{{ old('nfse_percentual_trib_federal', $setting->nfse_percentual_trib_federal) }}">
                                            <small class="text-muted">Disclosure da Lei da Transparência Fiscal - pergunte ao seu contador (varia por faixa do Simples Nacional).</small>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary">{{ __('Salvar Nota Fiscal') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#testStorageBtn').on('click', function () {
                var $btn = $(this);
                var $result = $('#testStorageResult');
                $result.removeClass('text-success text-danger').text('Testando...');
                $btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.setting.storage.test') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        blob_account_name: $('#blob_account_name').val(),
                        blob_account_key: $('#blob_account_key').val(),
                        blob_container: $('#blob_container').val(),
                        blob_endpoint_suffix: $('#blob_endpoint_suffix').val(),
                    },
                    dataType: 'json',
                }).done(function (data) {
                    $result.addClass(data.ok ? 'text-success' : 'text-danger').text(data.message);
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Falha ao testar a conexão.';
                    $result.addClass('text-danger').text(message);
                }).always(function () {
                    $btn.prop('disabled', false);
                });
            });

            $('#testMailBtn').on('click', function () {
                var $btn = $(this);
                var $result = $('#testMailResult');
                var testTo = $('#test_mail_to').val();

                if (!testTo) {
                    $result.removeClass('text-success').addClass('text-danger').text('Informe um e-mail para receber o teste.');
                    return;
                }

                $result.removeClass('text-success text-danger').text('Enviando...');
                $btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.setting.mail.test') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        mail_host: $('#mail_host').val(),
                        mail_port: $('#mail_port').val(),
                        mail_username: $('#mail_username').val(),
                        mail_password: $('#mail_password').val(),
                        mail_encryption: $('#mail_encryption').val(),
                        mail_from_address: $('#mail_from_address').val(),
                        mail_from_name: $('#mail_from_name').val(),
                        test_to: testTo,
                    },
                    dataType: 'json',
                }).done(function (data) {
                    $result.addClass(data.ok ? 'text-success' : 'text-danger').text(data.message);
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Falha ao enviar o e-mail de teste.';
                    $result.addClass('text-danger').text(message);
                }).always(function () {
                    $btn.prop('disabled', false);
                });
            });

            $('#testNfseCertBtn').on('click', function () {
                var $btn = $(this);
                var $result = $('#testNfseCertResult');
                $result.removeClass('text-success text-danger').text('Testando...');
                $btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.setting.nfse.test') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        nfse_certificate_password: $('#nfse_certificate_password').val(),
                    },
                    dataType: 'json',
                }).done(function (data) {
                    $result.addClass(data.ok ? 'text-success' : 'text-danger').text(data.message);
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Falha ao testar o certificado.';
                    $result.addClass('text-danger').text(message);
                }).always(function () {
                    $btn.prop('disabled', false);
                });
            });
        });
    </script>
@endsection
