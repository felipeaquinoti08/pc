<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $proposal->title }}</title>
    <style>
        @page {
            margin: 3.3cm 2cm 2.4cm 2cm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Helvetica", "DejaVu Sans", Arial, sans-serif;
            color: #1f2430;
            font-size: 10pt;
            line-height: 1.58;
        }

        h1, h2, h3, h4, h5 {
            margin: 0;
            color: #12151c;
        }

        p {
            margin: 0 0 9px 0;
        }

        b {
            color: #12151c;
        }

        /* paleta: azul (primária), teal e violeta (rotação de destaque), âmbar (atenção) */

        /* ---------- header / footer (repetem em toda página) ---------- */

        .header {
            position: fixed;
            top: -2.9cm;
            left: 0;
            right: 0;
            height: 2.1cm;
        }

        .header-inner {
            display: table;
            width: 100%;
            padding-bottom: 9px;
            border-bottom: 2.25pt solid #2f6fed;
        }

        .header-left, .header-right {
            display: table-cell;
            vertical-align: bottom;
        }

        .header-right {
            text-align: right;
        }

        .header-mark {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2f6fed;
            margin-right: 5px;
        }

        .header-brand {
            color: #12151c;
            font-weight: bold;
            font-size: 9.5pt;
        }

        .header-sub {
            color: #97a0ad;
            font-size: 7.6pt;
        }

        .header-doc-title {
            color: #12151c;
            font-weight: bold;
            font-size: 8.6pt;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .header-doc-sub {
            color: #97a0ad;
            font-size: 7.6pt;
        }

        .footer {
            position: fixed;
            bottom: -1.9cm;
            left: 0;
            right: 0;
            height: 1.6cm;
            padding-top: 8px;
            border-top: 0.75pt solid #e2e6ee;
            font-size: 7.2pt;
            color: #a3aab5;
        }

        .footer-inner {
            display: table;
            width: 100%;
        }

        .footer-left, .footer-right {
            display: table-cell;
            vertical-align: top;
        }

        .footer-right {
            text-align: right;
            white-space: nowrap;
            width: 70px;
            color: #2f6fed;
            font-weight: bold;
        }

        .footer .page-number:after {
            content: "Página " counter(page);
        }

        /* ---------- capa / hero ---------- */

        .hero {
            background: #1c3f8f;
            color: #ffffff;
            border-radius: 10px;
            padding: 26px 28px;
            margin-bottom: 20px;
        }

        .hero .eyebrow {
            color: #cfe0ff;
            font-weight: bold;
            font-size: 8.5pt;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .hero .doc-title {
            font-size: 22pt;
            font-weight: bold;
            line-height: 1.24;
            margin-bottom: 8px;
            color: #ffffff;
        }

        .hero .doc-subtitle {
            color: #dce8ff;
            font-size: 11pt;
            margin-bottom: 0;
        }

        .kpi-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 0 20px -8px;
            page-break-inside: avoid;
        }

        .kpi-cell {
            display: table-cell;
            border-radius: 8px;
            padding: 12px 13px;
        }

        .kpi-cell.c1 { background: #eaf1fd; border-top: 3pt solid #2f6fed; }
        .kpi-cell.c2 { background: #e6fbf8; border-top: 3pt solid #0ea5a8; }
        .kpi-cell.c3 { background: #f1edfe; border-top: 3pt solid #8b5cf6; }
        .kpi-cell.c4 { background: #fff4e5; border-top: 3pt solid #f0973a; }

        .kpi-label {
            font-size: 7.2pt;
            color: #5b6472;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
            font-weight: bold;
        }

        .kpi-value {
            font-size: 13pt;
            font-weight: bold;
            color: #12151c;
            white-space: nowrap;
        }

        .about-box {
            background: #f5f7fb;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .about-box p {
            color: #454b57;
            margin-bottom: 0;
        }

        .parties-row {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin: 0 0 6px -10px;
            page-break-inside: avoid;
        }

        .party-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            background: #f5f7fb;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .party-label {
            display: inline-block;
            background: #2f6fed;
            color: #ffffff;
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 3px 8px;
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .party-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 3px;
        }

        .party-meta {
            font-size: 8.7pt;
            color: #5b6472;
        }

        /* ---------- seções ---------- */

        .section-head {
            display: table;
            width: 100%;
            margin-top: 28px;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .section-badge {
            display: table-cell;
            width: 34px;
            vertical-align: middle;
        }

        .section-badge span {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            border-radius: 50%;
            background: #2f6fed;
            color: #ffffff;
            font-weight: bold;
            font-size: 10.5pt;
            text-align: center;
        }

        .section-head-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 12px;
        }

        .section-number {
            color: #2f6fed;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .section-title {
            font-size: 15.5pt;
            font-weight: bold;
        }

        /* fases - cards coloridos com rotação de destaque */

        .phase {
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .phase.a1 { background: #eaf1fd; }
        .phase.a2 { background: #e6fbf8; }
        .phase.a3 { background: #f1edfe; }

        .phase-head {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .phase-num {
            display: table-cell;
            width: 24px;
            vertical-align: top;
        }

        .phase-num span {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            border-radius: 50%;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5pt;
            text-align: center;
        }

        .phase.a1 .phase-num span { background: #2f6fed; }
        .phase.a2 .phase-num span { background: #0ea5a8; }
        .phase.a3 .phase-num span { background: #8b5cf6; }

        .phase-head-text {
            display: table-cell;
            vertical-align: top;
            padding-left: 10px;
        }

        .phase-eyebrow {
            font-weight: bold;
            font-size: 7.6pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 2px;
        }

        .phase.a1 .phase-eyebrow { color: #2f6fed; }
        .phase.a2 .phase-eyebrow { color: #0ea5a8; }
        .phase.a3 .phase-eyebrow { color: #8b5cf6; }

        .phase-title {
            font-weight: bold;
            font-size: 10.5pt;
        }

        .phase ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .phase li {
            font-size: 9pt;
            margin-bottom: 4px;
            padding-left: 14px;
            text-indent: -14px;
        }

        .phase.a1 li:before { content: "● "; color: #2f6fed; font-size: 6pt; }
        .phase.a2 li:before { content: "● "; color: #0ea5a8; font-size: 6pt; }
        .phase.a3 li:before { content: "● "; color: #8b5cf6; font-size: 6pt; }

        /* cláusulas contratuais */

        .clause-label {
            display: inline-block;
            background: #eaf1fd;
            color: #2f6fed;
            font-weight: bold;
            font-size: 7.8pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 4px 10px;
            border-radius: 10px;
            margin-top: 22px;
            margin-bottom: 6px;
        }

        .clause-title {
            font-weight: bold;
            font-size: 12.5pt;
            margin: 0 0 9px 0;
        }

        ul.plain {
            margin: 0 0 10px 0;
            padding: 0;
            list-style: none;
        }

        ul.plain li {
            margin-bottom: 4px;
            padding-left: 14px;
            text-indent: -14px;
        }

        ul.plain li:before {
            content: "● ";
            color: #2f6fed;
            font-size: 6pt;
        }

        .attention-box {
            background: #fff4e5;
            border-radius: 8px;
            border-left: 4pt solid #f0973a;
            padding: 13px 16px;
            margin: 8px 0 18px 0;
            page-break-inside: avoid;
        }

        .attention-box .attention-tag {
            font-weight: bold;
            font-size: 7.8pt;
            color: #b8631a;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 5px;
        }

        .attention-box .attention-title {
            font-weight: bold;
            font-size: 10.5pt;
            margin-bottom: 7px;
        }

        .attention-box ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .attention-box li {
            font-size: 9.3pt;
            margin-bottom: 4px;
            padding-left: 14px;
            text-indent: -14px;
        }

        .attention-box li:before {
            content: "● ";
            color: #f0973a;
            font-size: 6pt;
        }

        .schedule-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 6px;
            margin: 8px 0 14px 0;
        }

        .schedule-table tr {
            page-break-inside: avoid;
        }

        .schedule-table td {
            padding: 9px 12px;
            background: #f5f7fb;
            font-size: 9.3pt;
            vertical-align: top;
        }

        .schedule-table td:first-child {
            border-radius: 6px 0 0 6px;
            width: 130px;
            font-weight: bold;
            color: #ffffff;
            background: #2f6fed;
            white-space: nowrap;
        }

        .schedule-table td:last-child {
            border-radius: 0 6px 6px 0;
        }

        .bank-box {
            background: #f5f7fb;
            border-radius: 8px;
            padding: 14px 18px;
            margin: 10px 0;
            font-size: 9.3pt;
            page-break-inside: avoid;
        }

        .bank-box .bank-label {
            font-weight: bold;
            font-size: 7.3pt;
            color: #2f6fed;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 8px;
        }

        .bank-box div {
            margin-bottom: 3px;
        }

        .split-bar {
            display: table;
            width: 100%;
            table-layout: fixed;
            height: 10px;
            border-radius: 5px;
            margin: 12px 0 14px 0;
        }

        .split-bar div {
            display: table-cell;
            height: 10px;
        }

        .split-bar .down {
            background: #2f6fed;
            border-radius: 5px 0 0 5px;
        }

        .split-bar .final {
            background: #0ea5a8;
            border-radius: 0 5px 5px 0;
        }

        .payment-row {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin: 0 0 8px -10px;
            page-break-inside: avoid;
        }

        .payment-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border-radius: 8px;
            padding: 13px 16px;
        }

        .payment-col.down { background: #eaf1fd; }
        .payment-col.final { background: #e6fbf8; }

        .payment-col .payment-tag {
            font-weight: bold;
            font-size: 7.3pt;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .payment-col.down .payment-tag { color: #2f6fed; }
        .payment-col.final .payment-tag { color: #0ea5a8; }

        .payment-col .payment-value {
            font-size: 15.5pt;
            font-weight: bold;
            color: #12151c;
            margin: 5px 0 5px 0;
        }

        .payment-col .payment-note {
            font-size: 8.3pt;
            color: #5b6472;
        }

        /* assinaturas */

        .signature-row {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 20px 0;
            margin: 42px 0 0 -20px;
            page-break-inside: avoid;
        }

        .signature-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: center;
            background: #f5f7fb;
            border-radius: 8px;
            padding: 18px 16px 16px 16px;
        }

        .signature-line {
            border-top: 1.5pt solid #2f6fed;
            margin-top: 40px;
            padding-top: 8px;
            font-size: 8.7pt;
            color: #5b6472;
        }

        .signature-line b {
            display: block;
            font-size: 10pt;
            color: #12151c;
            margin-bottom: 2px;
        }

        .preview-download-bar {
            background: #12151c;
            color: #eef1f6;
            padding: 10px 16px;
            text-align: right;
            margin-bottom: 14px;
        }

        .preview-download-bar a {
            color: #eef1f6;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>
    @unless ($forPdf ?? false)
        <div class="preview-download-bar">
            <a href="{{ route('admin.proposal.edit', $proposal->id) }}">← Voltar para edição</a>
        </div>
    @endunless

    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <span class="header-mark"></span>
                <span class="header-brand">{{ \App\Models\Proposal::CONTRATADO_NOME }}</span>
                <div class="header-sub">{{ \App\Models\Proposal::CONTRATADO_EMPRESA }}</div>
            </div>
            <div class="header-right">
                <div class="header-doc-title">Proposta Comercial</div>
                <div class="header-doc-sub">{{ $proposal->document_label }} · {{ $proposal->proposal_year }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-inner">
            <div class="footer-left">{{ \App\Models\Proposal::CONTRATADO_EMPRESA }} &nbsp;·&nbsp; Documento confidencial — conteúdo restrito às partes qualificadas neste instrumento.</div>
            <div class="footer-right page-number"></div>
        </div>
    </div>

    <div class="hero">
        <div class="eyebrow">Proposta Comercial</div>
        <div class="doc-title">{{ $proposal->title }}</div>
        @if ($proposal->subtitle)
            <div class="doc-subtitle">{{ $proposal->subtitle }}</div>
        @endif
    </div>

    <div class="kpi-row">
        <div class="kpi-cell c1">
            <div class="kpi-label">Valor Total</div>
            <div class="kpi-value">R$ {{ number_format($proposal->total_value, 2, ',', '.') }}</div>
        </div>
        <div class="kpi-cell c2">
            <div class="kpi-label">Sinal de Entrada</div>
            <div class="kpi-value">{{ rtrim(rtrim(number_format($proposal->down_payment_percent, 2, ',', '.'), '0'), ',') }}%</div>
        </div>
        <div class="kpi-cell c3">
            <div class="kpi-label">Parcela Final</div>
            <div class="kpi-value">{{ rtrim(rtrim(number_format($proposal->final_payment_percent, 2, ',', '.'), '0'), ',') }}%</div>
        </div>
        <div class="kpi-cell c4">
            <div class="kpi-label">Prazo Estimado</div>
            <div class="kpi-value">{{ $proposal->estimated_days ?: '—' }}</div>
        </div>
    </div>

    @if ($proposal->about_text)
        <div class="about-box">
            <p>{{ $proposal->about_text }}</p>
        </div>
    @endif

    <div class="parties-row">
        <div class="party-col">
            <div class="party-label">Contratante</div>
            <div class="party-name">{{ $proposal->customer->name }}</div>
            @if ($proposal->customer->document_number)
                <div class="party-meta">CNPJ {{ $proposal->customer->document_number }}</div>
            @endif
            @if ($proposal->contratante_responsavel)
                <div class="party-meta">Responsável {{ $proposal->contratante_responsavel }}</div>
            @endif
        </div>
        <div class="party-col">
            <div class="party-label">Contratado</div>
            <div class="party-name">{{ \App\Models\Proposal::CONTRATADO_NOME }}</div>
            <div class="party-meta">{{ \App\Models\Proposal::CONTRATADO_EMPRESA }}</div>
            <div class="party-meta">CNPJ {{ \App\Models\Proposal::CONTRATADO_CNPJ }}</div>
        </div>
    </div>

    @if ($proposal->phases->isNotEmpty())
        <div class="section-head">
            <div class="section-badge"><span>01</span></div>
            <div class="section-head-text">
                <div class="section-number">Visão Geral da Proposta</div>
                <div class="section-title">Escopo da Implantação</div>
            </div>
        </div>

        @foreach ($proposal->phases as $phase)
            @php $accent = 'a' . (($loop->iteration - 1) % 3 + 1); @endphp
            <div class="phase {{ $accent }}">
                <div class="phase-head">
                    <div class="phase-num"><span>{{ sprintf('%02d', $loop->iteration) }}</span></div>
                    <div class="phase-head-text">
                        <div class="phase-eyebrow">{{ $phase->title }}</div>
                        @if ($phase->subtitle)
                            <div class="phase-title">{{ $phase->subtitle }}</div>
                        @endif
                    </div>
                </div>
                <ul>
                    @foreach ($phase->items as $item)
                        <li>{{ $item->description }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    @endif

    <div class="section-head" style="page-break-before: always; margin-top: 0;">
        <div class="section-badge"><span>02</span></div>
        <div class="section-head-text">
            <div class="section-number">Instrumento Contratual</div>
            <div class="section-title">Proposta Comercial e Contrato de Prestação de Serviços</div>
        </div>
    </div>

    <p>Por este instrumento particular, as Partes qualificadas abaixo têm, entre si, justo e contratado o que se segue:</p>
    <p><b>CONTRATANTE:</b> {{ $proposal->customer->name }}, pessoa jurídica de direito privado{{ $proposal->customer->document_number ? ", inscrita no CNPJ sob o nº {$proposal->customer->document_number}" : '' }}{{ $proposal->contratante_responsavel ? ", neste ato representada por {$proposal->contratante_responsavel}" : '' }}.</p>
    <p><b>CONTRATADO:</b> {{ \App\Models\Proposal::CONTRATADO_EMPRESA }}, pessoa jurídica de direito privado, inscrita no CNPJ sob o nº {{ \App\Models\Proposal::CONTRATADO_CNPJ }}, neste ato representada por {{ strtoupper(\App\Models\Proposal::CONTRATADO_RESPONSAVEL) }}.</p>

    <div class="clause-label">Cláusula Primeira</div>
    <div class="clause-title">Do Objeto e Escopo do Projeto</div>
    @if ($proposal->object_intro)
        <p>{{ $proposal->object_intro }}</p>
    @endif

    @foreach ($proposal->phases as $phase)
        <p><b>{{ $phase->title }}@if ($phase->subtitle) – {{ $phase->subtitle }}@endif</b></p>
        <ul class="plain">
            @foreach ($phase->items as $item)
                <li>{{ $item->description }}</li>
            @endforeach
        </ul>
    @endforeach

    <div class="clause-label">Cláusula Segunda</div>
    <div class="clause-title">Das Exclusões de Escopo</div>
    <div class="attention-box">
        <div class="attention-tag">⚠ Ponto de Atenção</div>
        <div class="attention-title">Das Exclusões de Escopo</div>
        <p style="margin-bottom:0;">{{ \App\Models\Proposal::EXCLUSIONS_TEXT }}</p>
    </div>

    @if ($proposal->prerequisites->isNotEmpty())
        <div class="clause-label">Cláusula Terceira</div>
        <div class="clause-title">Dos Pré-Requisitos Técnicos Obrigatórios</div>
        <div class="attention-box">
            <div class="attention-tag">⚠ Ponto de Atenção</div>
            <div class="attention-title">Dos Pré-Requisitos Técnicos Obrigatórios</div>
            <p>{{ \App\Models\Proposal::PREREQUISITES_INTRO }}</p>
            <ul>
                @foreach ($proposal->prerequisites as $prerequisite)
                    <li>{{ $prerequisite->description }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($proposal->scheduleSteps->isNotEmpty())
        <div class="clause-label">Cláusula Quarta</div>
        <div class="clause-title">Do Cronograma e Prazos</div>
        <p>O prazo estimado para a execução das atividades é dividido nas seguintes etapas:</p>
        <table class="schedule-table">
            @foreach ($proposal->scheduleSteps as $step)
                <tr>
                    <td>{{ $step->duration_label }}</td>
                    <td>{{ $step->description }}</td>
                </tr>
            @endforeach
        </table>
        <p>{{ \App\Models\Proposal::SCHEDULE_NOTE }}</p>
    @endif

    <div class="clause-label">Cláusula Quinta</div>
    <div class="clause-title">Do Preço e Condições de Pagamento</div>
    <p>{{ \App\Models\Proposal::PAYMENT_INTRO }}</p>
    <p>O pagamento deverá ser realizado via PIX ou Depósito Bancário para os dados abaixo:</p>

    <div class="bank-box">
        <div class="bank-label">Dados Bancários para Pagamento</div>
        <div><b>Banco:</b> {{ \App\Models\Proposal::BANK_NAME }}</div>
        <div><b>Agência:</b> {{ \App\Models\Proposal::BANK_AGENCY }}</div>
        <div><b>Conta:</b> {{ \App\Models\Proposal::BANK_ACCOUNT }}</div>
        <div><b>Beneficiário:</b> {{ \App\Models\Proposal::BANK_BENEFICIARY }}</div>
        <div><b>Chave PIX / CNPJ:</b> {{ \App\Models\Proposal::BANK_PIX }}</div>
    </div>

    <p>O cronograma de desembolso ocorrerá nas seguintes condições:</p>

    <div class="split-bar">
        <div class="down" style="width: {{ (float) $proposal->down_payment_percent }}%;"></div>
        <div class="final" style="width: {{ (float) $proposal->final_payment_percent }}%;"></div>
    </div>

    <div class="payment-row">
        <div class="payment-col down">
            <div class="payment-tag">Sinal de Entrada · {{ rtrim(rtrim(number_format($proposal->down_payment_percent, 2, ',', '.'), '0'), ',') }}%</div>
            <div class="payment-value">R$ {{ number_format($proposal->downPaymentValue(), 2, ',', '.') }}</div>
            <div class="payment-note">{{ \App\Models\Proposal::DOWN_PAYMENT_NOTE }}</div>
        </div>
        <div class="payment-col final">
            <div class="payment-tag">Parcela Final · {{ rtrim(rtrim(number_format($proposal->final_payment_percent, 2, ',', '.'), '0'), ',') }}%</div>
            <div class="payment-value">R$ {{ number_format($proposal->finalPaymentValue(), 2, ',', '.') }}</div>
            <div class="payment-note">{{ \App\Models\Proposal::FINAL_PAYMENT_NOTE }}</div>
        </div>
    </div>

    <div class="clause-label">Cláusula Sexta</div>
    <div class="clause-title">Da Confidencialidade (NDA)</div>
    <p>{{ \App\Models\Proposal::CONFIDENTIALITY_TEXT }}</p>

    <div class="section-head" style="page-break-before: always; margin-top: 0;">
        <div class="section-badge"><span>03</span></div>
        <div class="section-head-text">
            <div class="section-number">Assinaturas</div>
            <div class="section-title">Validação e Aceite das Partes</div>
        </div>
    </div>

    <p>E, por estarem assim justas e contratadas, as partes assinam o presente instrumento em 02 (duas) vias de igual teor e forma.</p>
    <p>{{ \App\Models\Proposal::SIGNATURE_LOCATION }}, {{ now()->day }} de {{ \App\Models\Proposal::monthNamePt(now()->month) }} de {{ now()->year }}.</p>

    <div class="signature-row">
        <div class="signature-col">
            <div class="signature-line">
                <b>{{ $proposal->customer->name }}</b>
                Contratante
            </div>
        </div>
        <div class="signature-col">
            <div class="signature-line">
                <b>{{ \App\Models\Proposal::CONTRATADO_EMPRESA }}</b>
                Representada por {{ \App\Models\Proposal::CONTRATADO_RESPONSAVEL }} · Contratado
            </div>
        </div>
    </div>
</body>
</html>
