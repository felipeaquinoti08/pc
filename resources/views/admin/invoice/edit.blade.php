@extends('layouts.simple.master')

@section('title', 'Editar Fatura')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Faturas</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.invoice.index') }}">Faturas</a></li>
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
                        <form class="row g-3 custom-input" action="{{ route('admin.invoice.update', $invoice->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            @include('admin.invoice.fields')
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Pagamento</h5>
                        <p class="text-muted mb-0">Tolerância até {{ $invoice->grace_period_ends_at->format('d/m/Y') }} antes da licença ser suspensa por falta de pagamento.</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="mb-3">
                                    <label class="form-label">Link de pagamento (Mercado Pago)</label>
                                    @if ($invoice->mp_payment_link)
                                        <input class="form-control" type="text" value="{{ $invoice->mp_payment_link }}" readonly onclick="this.select()">
                                    @else
                                        <input class="form-control" type="text" value="Nenhum link gerado ainda" readonly disabled>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label class="form-label d-block">Pago em</label>
                                    <span>
                                        {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : '—' }}
                                        @if ($invoice->paid_at && $invoice->is_test_payment)
                                            <span class="badge bg-info">TESTE</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <form action="{{ route('admin.invoice.regenerate-link', $invoice->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">{{ __('Gerar/Atualizar link de pagamento') }}</button>
                            </form>

                            @if ($invoice->status !== 'paid')
                                <form action="{{ route('admin.invoice.mark-paid', $invoice->id) }}" method="POST"
                                    onsubmit="return confirm('Marcar esta fatura como paga? Se a licença estiver suspensa por causa dela, será reativada.');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success">{{ __('Marcar como paga') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Nota Fiscal (NFS-e)</h5>
                        <p class="text-muted mb-0">Emissão manual, direto no Ambiente Nacional (nfse.gov.br).</p>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <label class="form-label d-block">Status</label>
                                @php
                                    $nfseLabels = [
                                        'not_issued' => '<span class="badge bg-secondary">Não emitida</span>',
                                        'issued' => '<span class="badge bg-success">Emitida</span>',
                                        'error' => '<span class="badge bg-danger">Erro</span>',
                                        'cancelled' => '<span class="badge bg-dark">Cancelada</span>',
                                    ];
                                @endphp
                                {!! $nfseLabels[$invoice->nfse_status] ?? $invoice->nfse_status !!}
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label d-block">Número</label>
                                <span>{{ $invoice->nfse_numero ?: '—' }}</span>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label d-block">Emitida em</label>
                                <span>{{ $invoice->nfse_issued_at?->format('d/m/Y H:i') ?: '—' }}</span>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label d-block">Chave de acesso</label>
                                {{-- 50 dígitos sem espaço - sem quebra própria ele estoura a
                                     coluna e sobrepõe o conteúdo vizinho. --}}
                                <span style="word-break: break-all;">{{ $invoice->nfse_chave_acesso ?: '—' }}</span>
                            </div>
                        </div>

                        @if ($invoice->nfse_status === 'error' && $invoice->nfse_error_message)
                            <div class="alert alert-danger">{{ $invoice->nfse_error_message }}</div>
                        @endif

                        <div class="d-flex gap-2 flex-wrap">
                            <form id="issue-nfse-form" action="{{ route('admin.invoice.issue-nfse', $invoice->id) }}" method="POST"
                                onsubmit="return confirm('Emitir a Nota Fiscal desta fatura agora? Isso envia a DPS pro governo de verdade (ambiente {{ \App\Models\Setting::current()->nfseIsProduction() ? 'PRODUÇÃO' : 'homologação' }}).');">
                                @csrf
                                @if ($invoice->nfse_status === 'issued')
                                    {{-- .btn-primary:disabled is forced full-color !important by this
                                         theme (style.css), so a disabled primary button looks identical
                                         to an active one - swap the class instead of relying on that
                                         state to actually look inactive. --}}
                                    <button type="submit" class="btn btn-secondary" disabled>Nota já emitida</button>
                                @else
                                    <button type="submit" class="btn btn-primary">
                                        {{ $invoice->nfse_status === 'error' ? 'Tentar emitir novamente' : 'Emitir Nota Fiscal' }}
                                    </button>
                                @endif
                            </form>

                            @if ($invoice->nfse_pdf_path)
                                <a class="btn btn-outline-secondary" href="{{ route('admin.invoice.nfse-pdf', $invoice->id) }}">Baixar PDF</a>
                            @elseif ($invoice->nfse_status === 'issued')
                                <form action="{{ route('admin.invoice.nfse-pdf.refresh', $invoice->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary">Buscar PDF</button>
                                </form>

                                {{-- Fallback: o endpoint do governo (ADN) pra buscar o PDF é
                                     instável e às vezes fica fora por um bom tempo mesmo com a
                                     nota já emitida e disponível pra download manual em
                                     nfse.gov.br/EmissorNacional - deixa anexar esse PDF aqui. --}}
                                <form action="{{ route('admin.invoice.nfse-pdf.upload', $invoice->id) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <input class="form-control form-control-sm" type="file" name="pdf" accept="application/pdf" required style="max-width: 220px;">
                                    <button type="submit" class="btn btn-outline-secondary">Enviar PDF baixado manualmente</button>
                                </form>
                                @error('pdf')
                                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                                @enderror
                            @endif

                            @if ($invoice->nfse_xml_path)
                                <a class="btn btn-outline-secondary" href="{{ route('admin.invoice.nfse-xml', $invoice->id) }}">Baixar XML</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
