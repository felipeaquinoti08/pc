@extends('emails.layout')

@section('content')
    @php
        $competencia = \Illuminate\Support\Carbon::createFromDate($invoice->reference_year, $invoice->reference_month, 1)->locale('pt_BR')->isoFormat('MMMM [de] YYYY');
    @endphp
    <p>Olá, {{ $invoice->customer?->name }}!</p>
    <p>Sua licença foi <strong>suspensa</strong> por falta de pagamento da fatura referente a <strong>{{ $competencia }}</strong>, vencida em {{ $invoice->due_date->format('d/m/Y') }}.</p>
    <p>
        <strong>Referente a:</strong> {{ $invoice->billingPolicy?->label() }}<br>
        <strong>Valor em aberto:</strong> R$ {{ number_format($invoice->amount, 2, ',', '.') }}
    </p>
    @if ($invoice->mp_payment_link)
        <p style="margin:24px 0;">
            <a href="{{ $invoice->mp_payment_link }}" style="background-color:#7366ff;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:6px;display:inline-block;">Pagar e reativar</a>
        </p>
    @endif
    <p>Assim que o pagamento for identificado, a licença é reativada automaticamente.</p>
@endsection
