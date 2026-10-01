@extends('layouts.simple.master')

@section('title', 'Revisar Importação')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Importar Nota Fiscal</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.invoice.index') }}">Faturas</a></li>
                        <li class="breadcrumb-item active">Revisar Importação</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Dados lidos do XML</h5>
                        <p class="text-muted mb-0">Confira antes de confirmar - nada foi salvo ainda.</p>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <label class="form-label d-block">Chave de acesso</label>
                                <span style="word-break: break-all;">{{ $parsed['chave_acesso'] }}</span>
                            </div>
                            <div class="col-sm-2">
                                <label class="form-label d-block">Número</label>
                                <span>{{ $parsed['numero'] ?: '—' }}</span>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label d-block">Emitida em</label>
                                <span>{{ $parsed['issued_at']?->format('d/m/Y H:i') ?: '—' }}</span>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label d-block">Competência (serviço)</label>
                                <span>{{ $parsed['competencia']?->format('m/Y') ?: '—' }}</span>
                            </div>
                        </div>

                        <form class="row g-3 custom-input" action="{{ route('admin.nfse_import.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="xml_base64" value="{{ $xmlBase64 }}">
                            <input type="hidden" name="chave_acesso" value="{{ $parsed['chave_acesso'] }}">
                            <input type="hidden" name="numero" value="{{ $parsed['numero'] }}">
                            <input type="hidden" name="issued_at" value="{{ $parsed['issued_at']?->format('Y-m-d\TH:i:s') }}">

                            <div class="col-12"><h6 class="mb-0">Cliente</h6></div>
                            <div class="col-sm-6">
                                <div class="mt-3">
                                    <label class="form-label">Cliente<span> *</span></label>
                                    <select class="form-select" name="customer_id" id="import-customer-select">
                                        <option value="" {{ ! $matchedCustomer ? 'selected' : '' }}>+ Criar novo cliente</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $matchedCustomer && $matchedCustomer->id === $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} ({{ $customer->document_number ?: 'sem documento' }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        @if ($matchedCustomer)
                                            Casado automaticamente pelo CNPJ/CPF do XML.
                                        @else
                                            Nenhum cliente com esse documento ({{ $parsed['tomador']['document_number'] }}) foi encontrado - um novo será criado com os dados abaixo, a menos que você selecione um existente.
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <div id="import-new-customer-fields" class="col-12 row g-3" style="display: {{ $matchedCustomer ? 'none' : 'flex' }};">
                                <div class="col-sm-4">
                                    <div class="mt-3">
                                        <label class="form-label">Nome do novo cliente<span> *</span></label>
                                        <input class="form-control" type="text" name="new_customer_name" value="{{ old('new_customer_name', $parsed['tomador']['name']) }}">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="mt-3">
                                        <label class="form-label">Tipo de documento</label>
                                        <select class="form-select" name="new_customer_document_type">
                                            <option value="cnpj" {{ $parsed['tomador']['document_type'] === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                                            <option value="cpf" {{ $parsed['tomador']['document_type'] === 'cpf' ? 'selected' : '' }}>CPF</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="mt-3">
                                        <label class="form-label">Documento</label>
                                        <input class="form-control" type="text" name="new_customer_document_number" value="{{ old('new_customer_document_number', $parsed['tomador']['document_number']) }}">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="mt-3">
                                        <label class="form-label">E-mail</label>
                                        <input class="form-control" type="email" name="new_customer_email" value="{{ old('new_customer_email', $parsed['tomador']['email']) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-3"><h6 class="mb-0">Fatura</h6></div>
                            <input type="hidden" name="invoice_id" id="import-invoice-id-hidden" value="{{ old('invoice_id', $matchedInvoice?->id) }}">
                            <div class="col-sm-8">
                                <div class="mt-3">
                                    <label class="form-label">Referente a<span> *</span></label>
                                    <select class="form-select" name="billing_policy_id" id="import-policy-select">
                                        <option value="" data-customer-id=""
                                            {{ old('billing_policy_id', $matchedInvoice?->billing_policy_id) ? '' : 'selected' }}>
                                            Fatura avulsa (sem serviço/licenciamento)</option>
                                        @foreach ($billingPolicies as $policy)
                                            <option value="{{ $policy->id }}" data-customer-id="{{ $policy->customer_id }}"
                                                {{ old('billing_policy_id', $matchedInvoice?->billing_policy_id) == $policy->id ? 'selected' : '' }}>
                                                {{ $policy->customer?->name }} — {{ $policy->label() }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="import-invoice-info">
                                        @if ($matchedInvoice)
                                            Fatura existente dessa competência será atualizada com os dados desta nota.
                                        @else
                                            Nenhuma fatura encontrada pra essa competência - uma nova será criada.
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="mt-3" id="import-invoice-warning" style="display: {{ $matchedInvoice?->nfse_chave_acesso ? 'block' : 'none' }};">
                                    <div class="alert alert-warning mb-0">Essa fatura já tem uma nota fiscal vinculada - confirmar vai substituir a referência antiga.</div>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="mt-3">
                                    <label class="form-label">Valor (R$)<span> *</span></label>
                                    <input class="form-control" type="number" step="0.01" min="0" name="amount" id="import-amount"
                                        value="{{ old('amount', $matchedInvoice?->amount ?? $parsed['amount']) }}">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="mt-3">
                                    <label class="form-label">Mês Comp.<span> *</span></label>
                                    <input class="form-control" type="number" min="1" max="12" name="competencia_month" id="import-competencia-month"
                                        value="{{ old('competencia_month', $competenciaDate->month) }}">
                                    <small class="text-muted">Mês do serviço em si.</small>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="mt-3">
                                    <label class="form-label">Ano Comp.<span> *</span></label>
                                    <input class="form-control" type="number" name="competencia_year" id="import-competencia-year"
                                        value="{{ old('competencia_year', $competenciaDate->year) }}">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="mt-3">
                                    <label class="form-label">Vencimento<span> *</span></label>
                                    <input class="form-control" type="date" name="due_date" id="import-due-date"
                                        value="{{ old('due_date', $matchedInvoice?->due_date?->format('Y-m-d') ?? $referenceDate->copy()->day(5)->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="mt-3">
                                    <label class="form-label">Status<span> *</span></label>
                                    <select class="form-select" name="status" id="import-status">
                                        <option value="paid" {{ old('status', $matchedInvoice?->status ?? 'paid') == 'paid' ? 'selected' : '' }}>Paga</option>
                                        <option value="pending" {{ old('status', $matchedInvoice?->status ?? '') == 'pending' ? 'selected' : '' }}>Pendente</option>
                                        <option value="overdue" {{ old('status', $matchedInvoice?->status ?? '') == 'overdue' ? 'selected' : '' }}>Atrasada</option>
                                        <option value="cancelled" {{ old('status', $matchedInvoice?->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                                    </select>
                                    <small class="text-muted">Nota antiga geralmente já foi paga - ajuste se não for o caso.</small>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="mt-3">
                                    <label class="form-label">Data de Pagamento</label>
                                    <input class="form-control" type="date" name="paid_at" id="import-paid-at"
                                        value="{{ old('paid_at', $matchedInvoice?->paid_at?->format('Y-m-d') ?? $parsed['issued_at']?->format('Y-m-d')) }}">
                                    <small class="text-muted">Sugerido a partir da data de emissão - ajuste pra data real do pagamento.</small>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="mt-3">
                                    <label class="form-label">Descrição do Serviço</label>
                                    <textarea class="form-control" name="notes" rows="3">{{ old('notes', $matchedInvoice?->notes ?? $parsed['description']) }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary">Confirmar Importação</button>
                                <a href="{{ route('admin.nfse_import.create') }}" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>

                        <script type="application/json" id="import-invoices-data">{!! $invoices->map(function ($inv) {
                            return [
                                'id' => $inv->id,
                                'customer_id' => $inv->customer_id,
                                'billing_policy_id' => $inv->billing_policy_id,
                                'reference_month' => $inv->reference_month,
                                'reference_year' => $inv->reference_year,
                                'amount' => (string) $inv->amount,
                                'due_date' => $inv->due_date->format('Y-m-d'),
                                'status' => $inv->status,
                                'paid_at' => $inv->paid_at?->format('Y-m-d'),
                                'notes' => $inv->notes,
                                'has_nfse' => (bool) $inv->nfse_chave_acesso,
                            ];
                        })->values()->toJson() !!}</script>
                        <script type="application/json" id="import-parsed-defaults">{!! json_encode([
                            'amount' => (string) $parsed['amount'],
                            'paid_at' => $parsed['issued_at']?->format('Y-m-d'),
                            'notes' => $parsed['description'],
                        ]) !!}</script>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var select = document.getElementById('import-customer-select');
            var newFields = document.getElementById('import-new-customer-fields');

            function sync() {
                newFields.style.display = select.value === '' ? 'flex' : 'none';
            }

            select.addEventListener('change', sync);
            sync();
        })();

        (function () {
            var customerSelect = document.getElementById('import-customer-select');
            var policySelect = document.getElementById('import-policy-select');
            var invoiceIdField = document.getElementById('import-invoice-id-hidden');
            var warning = document.getElementById('import-invoice-warning');
            var info = document.getElementById('import-invoice-info');
            var competenciaMonthField = document.getElementById('import-competencia-month');
            var competenciaYearField = document.getElementById('import-competencia-year');
            var fields = {
                amount: document.getElementById('import-amount'),
                dueDate: document.getElementById('import-due-date'),
                status: document.getElementById('import-status'),
                paidAt: document.getElementById('import-paid-at'),
                notes: document.querySelector('textarea[name="notes"]'),
            };

            var invoices = JSON.parse(document.getElementById('import-invoices-data').textContent);
            var parsedDefaults = JSON.parse(document.getElementById('import-parsed-defaults').textContent);

            // A política pertence a um único cliente - some com as que não
            // são do cliente selecionado em vez de deixar escolher um
            // serviço/licenciamento de outra empresa.
            function filterPolicyOptions() {
                var customerId = customerSelect.value;
                var selectedStillVisible = false;

                Array.prototype.forEach.call(policySelect.options, function (option) {
                    var matches = option.value === '' || option.dataset.customerId === customerId;
                    option.hidden = !matches;
                    option.disabled = !matches;
                    if (matches && option.selected) {
                        selectedStillVisible = true;
                    }
                });

                if (!selectedStillVisible) {
                    policySelect.value = '';
                }
            }

            // reference_month/year é sempre a competência + 1 (regra de
            // faturamento em ciclo adiantado, ver Invoice::servicePeriod()).
            function referencePeriod() {
                var month = parseInt(competenciaMonthField.value, 10);
                var year = parseInt(competenciaYearField.value, 10);

                if (!month || !year) {
                    return null;
                }

                month += 1;
                if (month > 12) {
                    month = 1;
                    year += 1;
                }

                return { month: month, year: year };
            }

            function findInvoice(period) {
                var customerId = customerSelect.value;
                var policyId = policySelect.value;

                if (!customerId || !period) {
                    return null;
                }

                return invoices.find(function (inv) {
                    return String(inv.customer_id) === String(customerId)
                        && String(inv.billing_policy_id || '') === String(policyId || '')
                        && inv.reference_month === period.month
                        && inv.reference_year === period.year;
                }) || null;
            }

            function sync() {
                filterPolicyOptions();

                var period = referencePeriod();
                var match = findInvoice(period);

                if (match) {
                    invoiceIdField.value = match.id;
                    fields.amount.value = match.amount;
                    fields.dueDate.value = match.due_date;
                    fields.status.value = match.status;
                    fields.paidAt.value = match.paid_at || '';
                    fields.notes.value = match.notes || '';
                    warning.style.display = match.has_nfse ? 'block' : 'none';
                    info.textContent = 'Fatura existente dessa competência será atualizada com os dados desta nota.';
                } else {
                    invoiceIdField.value = '';
                    fields.amount.value = parsedDefaults.amount;
                    if (period) {
                        fields.dueDate.value = period.year + '-' + String(period.month).padStart(2, '0') + '-05';
                    }
                    fields.status.value = 'paid';
                    fields.paidAt.value = parsedDefaults.paid_at || '';
                    fields.notes.value = parsedDefaults.notes || '';
                    warning.style.display = 'none';
                    info.textContent = 'Nenhuma fatura encontrada pra essa competência - uma nova será criada.';
                }
            }

            customerSelect.addEventListener('change', sync);
            policySelect.addEventListener('change', sync);
            competenciaMonthField.addEventListener('change', sync);
            competenciaYearField.addEventListener('change', sync);
        })();
    </script>
@endsection
