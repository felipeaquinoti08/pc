@extends('emails.layout')

@section('content')
    @php
        $competencia = \Illuminate\Support\Carbon::createFromDate($invoice->reference_year, $invoice->reference_month, 1)->locale('pt_BR')->isoFormat('MMMM [de] YYYY');
    @endphp
    <p>Olá, {{ $invoice->customer?->name }}!</p>
    <p>Uma nova fatura referente a <strong>{{ $competencia }}</strong> foi gerada.</p>
    <p>
        <strong>Referente a:</strong> {{ $invoice->billingPolicy?->label() }}<br>
        <strong>Valor:</strong> R$ {{ number_format($invoice->amount, 2, ',', '.') }}<br>
        <strong>Vencimento:</strong> {{ $invoice->due_date->format('d/m/Y') }}
    </p>
    @if ($invoice->mp_payment_link)
        <p style="margin:24px 0;">
            <a href="{{ $invoice->mp_payment_link }}" style="background-color:#7366ff;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:6px;display:inline-block;">Pagar fatura</a>
        </p>
    @endif
    <p>Após o vencimento, a licença fica sujeita a suspensão caso o pagamento não seja identificado dentro do prazo de tolerância.</p>
@endsection
