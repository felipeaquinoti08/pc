@extends('layouts.simple.master')

@section('title', 'Importar Ativos (GLPI)')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Importar Ativos (GLPI)</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item active">Importar Ativos (GLPI)</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xxl-5 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Padrão do CSV</h5>
                        <p class="mb-0">Não importa qual sistema de inventário o cliente usa hoje (planilha própria, InvGate, outro) — o arquivo enviado aqui precisa seguir sempre este mesmo modelo de colunas. Baixe o modelo, adapte o inventário do cliente pra ele, e envie ao lado.</p>
                        <p class="mb-0">Os nomes das colunas (primeira linha) são fixos, em minúsculo e sem acento — não altere o cabeçalho do modelo. Já os dados de cada linha podem ter acento normalmente (ex: "São Paulo"); se o Excel salvar o arquivo em outro formato de texto, o sistema detecta e corrige sozinho na hora de ler.</p>
                    </div>
                    <div class="card-body">
                        @php
                            // Query string com o mtime do arquivo - cada vez que o
                            // template mudar, o link muda junto, então o Cloudflare
                            // (que fica na frente deste domínio) nunca serve uma
                            // cópia antiga em cache pra esse endereço.
                            $templatePath = public_path('templates/glpi_asset_import_template.csv');
                            $templateVersion = file_exists($templatePath) ? filemtime($templatePath) : time();
                        @endphp
                        <a href="{{ asset('templates/glpi_asset_import_template.csv') }}?v={{ $templateVersion }}" class="btn btn-outline-primary mb-3" download>
                            <i class="fa-solid fa-download me-1"></i> Baixar modelo CSV
                        </a>

                        <div class="table-responsive custom-scrollbar">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Coluna</th>
                                        <th>Obrigatório</th>
                                        <th>O que é</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>tipo_ativo</code></td>
                                        <td><span class="badge bg-danger">Sim</span></td>
                                        <td>Um de: <strong>{{ implode(', ', \App\Services\Glpi\GlpiAssetImportService::recognizedTypes()) }}</strong>. Define em qual módulo do GLPI o ativo entra (ex: "Chip" vira Linha Telefônica, já que o GLPI não tem um cadastro de "Chip" separado).</td>
                                    </tr>
                                    <tr>
                                        <td><code>nome</code></td>
                                        <td><span class="badge bg-danger">Sim</span></td>
                                        <td>Nome/identificação do ativo (o que aparece na listagem do GLPI).</td>
                                    </tr>
                                    <tr>
                                        <td><code>codigo_referencia</code></td>
                                        <td><span class="badge bg-danger">Sim</span></td>
                                        <td>Um código único e estável do inventário de origem (ex: o ID que o sistema antigo já usava, ou o próprio número de série se não houver outro). É essa coluna que garante que reimportar o mesmo CSV <strong>atualiza</strong> em vez de duplicar o ativo.</td>
                                    </tr>
                                    <tr>
                                        <td><code>numero_serie</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td>Número de série do fabricante, se houver.</td>
                                    </tr>
                                    <tr>
                                        <td><code>status</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td>Ex: Ativo, Em estoque, Descarte, Em reparo. Qualquer valor preenchido aqui vira uma opção real no GLPI (criada na hora, se ainda não existir).</td>
                                    </tr>
                                    <tr>
                                        <td><code>responsavel</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td>Nome de quem usa o ativo. Fica só como anotação de texto no ativo (não cria usuário no GLPI).</td>
                                    </tr>
                                    <tr>
                                        <td><code>localizacao</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td>Unidade/cidade/sala. Criada no GLPI se ainda não existir. <strong>Escreva sempre igual</strong> pro mesmo lugar (mesmo acento/maiúscula) — "São Paulo" e "SAO PAULO" viram duas localizações diferentes.</td>
                                    </tr>
                                    <tr>
                                        <td><code>fabricante</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td>Ex: Dell, LG, Samsung. Criado no GLPI se ainda não existir (mesmo cuidado de escrita acima).</td>
                                    </tr>
                                    <tr>
                                        <td><code>modelo</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td>Ex: Latitude 5420. Criado no GLPI se ainda não existir. Não se aplica a "Chip".</td>
                                    </tr>
                                    <tr>
                                        <td><code>patrimonio</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td>Código de patrimônio da empresa, se houver.</td>
                                    </tr>
                                    <tr>
                                        <td><code>termo_assinado</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td><strong>sim</strong> ou <strong>nao</strong>. Vira uma anotação no campo "Comentário" do ativo.</td>
                                    </tr>
                                    <tr>
                                        <td><code>link_termo</code></td>
                                        <td><span class="badge bg-secondary">Não</span></td>
                                        <td>Link do documento assinado (ex: Assinei, DocuSign), se houver.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-7 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <p class="mb-0">Nada é gravado no GLPI nesta etapa — a próxima tela mostra uma prévia (quantos ativos, quantos cadastros novos de apoio) antes de confirmar.</p>
                    </div>
                    <div class="card-body">
                        @if ($instances->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Nenhuma instância GLPI ativa cadastrada ainda. <a href="{{ route('admin.glpi_instance.create') }}">Cadastre uma primeiro</a>.
                            </div>
                        @else
                            <form class="row g-3 custom-input" action="{{ route('admin.glpi_asset_import.analyze') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="col-sm-6">
                                    <label class="form-label">Instância GLPI<span> *</span></label>
                                    <select class="form-select" name="glpi_instance_id">
                                        <option value="">Selecione</option>
                                        @foreach ($instances as $instance)
                                            <option value="{{ $instance->id }}">{{ $instance->name }} ({{ $instance->base_url }})</option>
                                        @endforeach
                                    </select>
                                    @error('glpi_instance_id')
                                        <span class="text-danger"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Arquivo CSV<span> *</span></label>
                                    <input class="form-control" type="file" name="csv" accept=".csv,text/csv">
                                    <small class="text-muted">Precisa seguir o modelo de colunas ao lado.</small>
                                    @error('csv')
                                        <span class="text-danger"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-primary">Analisar</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
