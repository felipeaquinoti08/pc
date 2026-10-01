<div class="form theme-form">
    <div class="row">
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Cliente</label>
                <input class="form-control" type="text" value="{{ $invoice->customer?->name }}" readonly disabled>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Referente a</label>
                <select class="form-select" name="billing_policy_id">
                    <option value="" {{ old('billing_policy_id', $invoice->billing_policy_id) == '' ? 'selected' : '' }}>Fatura avulsa</option>
                    @foreach ($billingPolicies as $policy)
                        <option value="{{ $policy->id }}" {{ old('billing_policy_id', $invoice->billing_policy_id) == $policy->id ? 'selected' : '' }}>
                            {{ $policy->label() }}</option>
                    @endforeach
                </select>
                @error('billing_policy_id')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Competência (ciclo de cobrança)</label>
                <input class="form-control" type="text" value="{{ sprintf('%02d/%d', $invoice->reference_month, $invoice->reference_year) }}" readonly disabled>
            </div>
        </div>
    </div>

    @isset($invoice)
        @if ($invoice->exists && $invoice->nfse_status !== 'issued')
            <div class="row">
                <div class="col-sm-4">
                    <div class="mt-3">
                        <label class="form-label d-block">Competência da Nota Fiscal (período do serviço)</label>
                        @php
                            $servicePeriod = $invoice->servicePeriod();
                        @endphp
                        <div class="d-flex gap-1">
                            {{-- Esses dois campos pertencem ao formulário de emissão da nota
                                 (mais abaixo, no card "Nota Fiscal"), não a este de edição da
                                 fatura - o atributo form="" os associa a ele mesmo ficando
                                 visualmente aqui em cima. --}}
                            <select class="form-select" name="competencia_month" form="issue-nfse-form">
                                @foreach (['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'] as $i => $label)
                                    <option value="{{ $i + 1 }}" {{ $servicePeriod->month === $i + 1 ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="number" class="form-control" name="competencia_year" value="{{ $servicePeriod->year }}" form="issue-nfse-form" style="max-width: 110px;">
                        </div>
                        <small class="text-muted">Usada ao emitir a Nota Fiscal (campo dCompet) - sempre o mês anterior à competência de cobrança acima (o período de serviço de fato). Ajuste aqui se precisar de um valor diferente.</small>
                    </div>
                </div>
            </div>
        @endif
    @endisset

    <div class="row">
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Valor (R$)<span> *</span></label>
                <input class="form-control" type="number" step="0.01" min="0" name="amount"
                    value="{{ old('amount', $invoice->amount ?? '') }}">
                @error('amount')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Vencimento<span> *</span></label>
                <input class="form-control" type="date" name="due_date"
                    value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}">
                <small class="text-muted">O prazo de tolerância (5 dias úteis) é recalculado a partir desta data.</small>
                @error('due_date')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Status<span> *</span></label>
                <select class="form-select" name="status">
                    <option value="pending" {{ old('status', $invoice->status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="overdue" {{ old('status', $invoice->status ?? '') == 'overdue' ? 'selected' : '' }}>Atrasada</option>
                    <option value="paid" {{ old('status', $invoice->status ?? '') == 'paid' ? 'selected' : '' }}>Paga</option>
                    <option value="cancelled" {{ old('status', $invoice->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                </select>
                @error('status')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Data de Pagamento</label>
                <input class="form-control" type="date" name="paid_at"
                    value="{{ old('paid_at', $invoice->paid_at?->format('Y-m-d')) }}">
                <small class="text-muted">Ajuste para a data real do pagamento — importante ao corrigir faturas retroativas.</small>
                @error('paid_at')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="mt-3">
                <label class="form-label">Descrição do Serviço</label>
                <textarea class="form-control" name="notes">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                <small class="text-muted">Pré-preenchida com a competência do mês anterior ao vencimento — é o texto usado como referência para emissão da nota fiscal.</small>
            </div>
        </div>
    </div>

    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
    </div>
</div>
