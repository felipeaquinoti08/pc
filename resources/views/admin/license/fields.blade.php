<div class="form theme-form">
    @isset($license)
        <div class="mt-3">
            <label class="form-label">Chave da Licença</label>
            <input class="form-control" type="text" value="{{ $license->license_key }}" readonly disabled>
        </div>
    @endisset

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Cliente<span> *</span></label>
                <select class="form-select" name="customer_id">
                    <option value="">Selecione</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $license->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
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
                <label class="form-label">Serviço(s)<span> *</span></label>
                @php
                    $selectedServiceIds = old('service_ids', isset($license) ? $license->services->pluck('id')->all() : []);
                @endphp
                <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
                    @foreach ($services as $service)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                id="service-{{ $service->id }}" {{ in_array($service->id, $selectedServiceIds) ? 'checked' : '' }}>
                            <label class="form-check-label" for="service-{{ $service->id }}">{{ $service->name }}</label>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted">Marque mais de um pra criar um pacote - um único serial (Chave da Licença) libera todos os serviços marcados.</small>
                @error('service_ids')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Qtd. Instalações<span> *</span></label>
                <input class="form-control" type="number" min="1" value="{{ old('quantity', $license->quantity ?? 1) }}" name="quantity">
                @error('quantity')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Início<span> *</span></label>
                <input class="form-control" type="date" value="{{ old('starts_at', isset($license) ? $license->starts_at->format('Y-m-d') : date('Y-m-d')) }}" name="starts_at">
                @error('starts_at')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Vencimento</label>
                <input class="form-control" type="date" value="{{ old('expires_at', isset($license) && $license->expires_at ? $license->expires_at->format('Y-m-d') : '') }}" name="expires_at">
                <small class="text-muted">Vazio = sem vencimento</small>
                @error('expires_at')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-3">
        Cobrança fica a cargo das <a href="{{ route('admin.billing_policy.index') }}">Políticas de Faturamento</a> — esta tela cuida só de ativação/quantidade de instalações.
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Status<span> *</span></label>
                <select class="form-select" name="status">
                    <option value="active" {{ old('status', $license->status ?? 'active') == 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="suspended" {{ old('status', $license->status ?? '') == 'suspended' ? 'selected' : '' }}>Suspensa</option>
                    <option value="cancelled" {{ old('status', $license->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                    <option value="expired" {{ old('status', $license->status ?? '') == 'expired' ? 'selected' : '' }}>Expirada</option>
                </select>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="mt-3">
                <label class="form-label">Observações</label>
                <textarea class="form-control" name="notes">{{ old('notes', $license->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
    </div>
</div>
