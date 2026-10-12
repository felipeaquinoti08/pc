<div class="form theme-form">
    <div class="row">
        <div class="col-sm-8">
            <div class="mt-3">
                <label class="form-label">Nome<span> *</span></label>
                <input class="form-control" type="text" value="{{ old('name', $service->name ?? '') }}" placeholder="Nome do serviço" name="name">
                @error('name')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="1" {{ !(old('status', $service->status ?? 1)) == 0 ? 'selected' : '' }}>{{ __('Ativo') }}</option>
                    <option value="0" {{ (old('status', $service->status ?? 1)) == 0 ? 'selected' : '' }}>{{ __('Inativo') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8">
            <div class="mt-3">
                <label class="form-label">Slug (identificador fixo)@unless(isset($service))<small class="text-muted"> - opcional, gerado do nome se deixar em branco</small>@endunless</label>
                <input class="form-control" type="text" value="{{ old('slug', $service->slug ?? '') }}" placeholder="ex: entra-sso" name="slug">
                <small class="text-muted">Esse valor é o que integrações externas (ex: plugins GLPI) usam pra provar qual serviço estão validando ao consultar uma licença - não muda sozinho se você editar o nome depois, pra não quebrar quem já tem esse valor configurado.</small>
                @error('slug')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Descrição</label>
        <textarea class="form-control" name="description">{{ old('description', $service->description ?? '') }}</textarea>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Preço (R$)<span> *</span></label>
                <input class="form-control" type="number" step="0.01" min="0" value="{{ old('price', $service->price ?? '0.00') }}" name="price">
                @error('price')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Cobrança<span> *</span></label>
                <select class="form-select" name="billing_type">
                    <option value="one_time" {{ old('billing_type', $service->billing_type ?? '') == 'one_time' ? 'selected' : '' }}>Única</option>
                    <option value="monthly" {{ old('billing_type', $service->billing_type ?? '') == 'monthly' ? 'selected' : '' }}>Mensal</option>
                    <option value="yearly" {{ old('billing_type', $service->billing_type ?? '') == 'yearly' ? 'selected' : '' }}>Anual</option>
                </select>
            </div>
        </div>
    </div>

    @php
        $existingPlans = isset($service) ? $service->pricingPlans->pluck('price', 'frequency') : collect();
    @endphp
    <div class="col-12 mt-4"><h5 class="mb-0">Planos por Frequência</h5></div>
    <div class="row">
        <div class="col-sm-12">
            <p class="text-muted mt-2 mb-0">Opcional. Marque as frequências que este serviço oferece com um valor próprio (ex: Mensal R$30, Anual R$300) — serve de atalho ao criar uma Política de Faturamento pra este serviço, sem precisar duplicar o cadastro do serviço por frequência. O "Preço"/"Cobrança" acima continuam servindo só de referência padrão.</p>
            <div class="table-responsive mt-2">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Frequência</th>
                            <th style="width: 200px;">Valor (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Models\BillingPolicy::frequencies() as $value => $label)
                            @php
                                $existingPrice = old("pricing_plans.$value", $existingPlans[$value] ?? '');
                            @endphp
                            <tr>
                                <td>
                                    <input class="form-check-input service-plan-toggle" type="checkbox" data-for="plan-price-{{ $value }}" {{ $existingPrice !== '' ? 'checked' : '' }}>
                                </td>
                                <td>{{ $label }}</td>
                                <td>
                                    <input class="form-control form-control-sm" type="number" step="0.01" min="0"
                                        id="plan-price-{{ $value }}" name="pricing_plans[{{ $value }}]"
                                        value="{{ $existingPrice }}" {{ $existingPrice === '' ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('pricing_plans')
                <span class="text-danger"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
    </div>
</div>
