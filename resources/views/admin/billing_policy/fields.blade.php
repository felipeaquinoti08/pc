@php
    $selectedBillableType = old('billable_type', match ($policy->billable_type ?? null) {
        \App\Models\License::class => 'license',
        \App\Models\Service::class => 'service',
        \App\Models\Proposal::class => 'proposal',
        default => '',
    });
@endphp
<div class="form theme-form">
    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Cliente<span> *</span></label>
                <select class="form-select" name="customer_id">
                    <option value="">Selecione</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $policy->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}</option>
                    @endforeach
                </select>
                @error('customer_id')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Referente a</label>
                <select class="form-select" name="billable_type" id="billing-policy-billable-type">
                    <option value="" {{ $selectedBillableType === '' ? 'selected' : '' }}>Cobrança avulsa (sem licença/serviço)</option>
                    <option value="license" {{ $selectedBillableType === 'license' ? 'selected' : '' }}>Licença</option>
                    <option value="service" {{ $selectedBillableType === 'service' ? 'selected' : '' }}>Serviço</option>
                    @if ($selectedBillableType === 'proposal')
                        {{-- Gerada automaticamente ao aceitar uma proposta - não atribuível por aqui,
                             só listada pra não se perder ao salvar o formulário de edição. --}}
                        <option value="proposal" selected>Proposta — {{ $policy->billable?->title }}</option>
                    @endif
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 billing-policy-billable-field" data-for="license">
            <div class="mt-3">
                <label class="form-label">Licença</label>
                <select class="form-select" name="billable_id" id="billing-policy-license-id">
                    <option value="">Selecione</option>
                    @foreach ($licenses as $license)
                        <option value="{{ $license->id }}" {{ old('billable_id', $selectedBillableType === 'license' ? ($policy->billable_id ?? '') : '') == $license->id ? 'selected' : '' }}>
                            {{ $license->license_key }} — {{ $license->customer?->name }} ({{ $license->service?->name }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-12 billing-policy-billable-field" data-for="service">
            <div class="mt-3">
                <label class="form-label">Serviço</label>
                <select class="form-select" name="billable_id" id="billing-policy-service-id">
                    <option value="">Selecione</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" {{ old('billable_id', $selectedBillableType === 'service' ? ($policy->billable_id ?? '') : '') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-12 billing-policy-billable-field" data-for="proposal">
            @isset($policy)
                {{-- name="billable_id" duplicado de propósito com os selects de
                     license/service acima - o toggle em scripts.blade.php
                     desabilita todos os outros quando este está ativo, então só
                     um valor é submetido por vez. --}}
                <input type="hidden" name="billable_id" value="{{ $policy->billable_id }}">
                <p class="text-muted mb-0 mt-3">Gerada automaticamente ao aceitar a proposta <strong>{{ $policy->billable?->title }}</strong> — não pode ser reatribuída por aqui.</p>
            @endisset
        </div>
        <div class="col-sm-12 billing-policy-billable-field" data-for="">
            <div class="mt-3">
                <label class="form-label">Descrição<span> *</span></label>
                <input class="form-control" type="text" name="description"
                    value="{{ old('description', $policy->description ?? '') }}" placeholder="Ex: Consultoria avulsa de migração">
                @error('description')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Frequência<span> *</span></label>
                <select class="form-select" name="frequency">
                    @foreach (\App\Models\BillingPolicy::frequencies() as $value => $label)
                        <option value="{{ $value }}" {{ old('frequency', $policy->frequency ?? 'monthly') == $value ? 'selected' : '' }}>
                            {{ $label }}</option>
                    @endforeach
                </select>
                @error('frequency')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Valor (R$)<span> *</span></label>
                <input class="form-control" type="number" step="0.01" min="0" name="amount"
                    value="{{ old('amount', $policy->amount ?? '') }}">
                @error('amount')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Dia do Vencimento<span> *</span></label>
                <input class="form-control" type="number" min="1" max="28" name="due_day"
                    value="{{ old('due_day', $policy->due_day ?? 5) }}">
                <small class="text-muted">1 a 28, pra valer em qualquer mês.</small>
                @error('due_day')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Início<span> *</span></label>
                <input class="form-control" type="date" name="starts_at"
                    value="{{ old('starts_at', isset($policy) ? $policy->starts_at->format('Y-m-d') : now()->format('Y-m-d')) }}">
                <small class="text-muted">Âncora dos ciclos semestral/anual.</small>
                @error('starts_at')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Status<span> *</span></label>
                <select class="form-select" name="status">
                    <option value="active" {{ old('status', $policy->status ?? 'active') == 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="paused" {{ old('status', $policy->status ?? '') == 'paused' ? 'selected' : '' }}>Pausada</option>
                    <option value="cancelled" {{ old('status', $policy->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <label class="form-label mb-0">Descrição do Serviço</label>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" data-insert-variable="@{{competencia}}">Inserir competência</button>
                    <button type="button" class="btn btn-outline-secondary" data-insert-variable="@{{vencimento}}">Inserir vencimento</button>
                </div>
            </div>
            <textarea class="form-control" name="notes" id="billing-policy-notes" rows="6">{{ old('notes', $policy->notes ?? '') }}</textarea>
            <small class="text-muted">
                Usada como descrição de cada fatura gerada por esta política (e, futuramente, na nota fiscal). Deixe em branco para usar uma descrição automática simples.
                As variáveis <code>@{{competencia}}</code> (ex: JUN/26) e <code>@{{vencimento}}</code> (ex: 20/06/2026) são substituídas automaticamente em cada fatura.
            </small>
        </div>
    </div>

    @unless (isset($policy))
        <div class="alert alert-info mt-3">
            Se a frequência for <strong>Única</strong>, a fatura é gerada imediatamente ao salvar. Para as demais frequências, a fatura do ciclo é gerada automaticamente todo dia, de acordo com o dia de vencimento configurado.
        </div>
    @endunless

    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
    </div>
</div>
