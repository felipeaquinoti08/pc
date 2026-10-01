@extends('emails.layout')

@section('content')
    <p>Novo contato recebido pelo site.</p>
    <p>
        <strong>Nome:</strong> {{ $contactLead->name }}<br>
        <strong>E-mail:</strong> {{ $contactLead->email }}<br>
        <strong>Telefone:</strong> {{ $contactLead->phone ?: '-' }}<br>
        <strong>Empresa:</strong> {{ $contactLead->company ?: '-' }}
    </p>
    @if ($contactLead->message)
        <p><strong>Mensagem:</strong><br>{{ $contactLead->message }}</p>
    @endif
    <p style="margin:24px 0;">
        <a href="{{ route('admin.contact_lead.show', $contactLead->id) }}" style="background-color:#7366ff;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:6px;display:inline-block;">Ver no painel</a>
    </p>
@endsection
