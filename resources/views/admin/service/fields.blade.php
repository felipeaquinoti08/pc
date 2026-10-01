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

    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
    </div>
</div>
