@extends('emails.layout')

@section('content')
    @php
        $competencia = \Illuminate\Support\Carbon::createFromDate($invoice->reference_year, $invoice->reference_month, 1)->locale('pt_BR')->isoFormat('MMMM [de] YYYY');
    @endphp
    <p>Olá, {{ $invoice->customer?->name }}!</p>
    <p>Recebemos o pagamento da fatura referente a <strong>{{ $competencia }}</strong>. Obrigado!</p>
    <p>
        <strong>Referente a:</strong> {{ $invoice->billingPolicy?->label() }}<br>
        <strong>Valor:</strong> R$ {{ number_format($invoice->amount, 2, ',', '.') }}<br>
        <strong>Pago em:</strong> {{ $invoice->paid_at?->format('d/m/Y H:i') }}
    </p>
@endsection
