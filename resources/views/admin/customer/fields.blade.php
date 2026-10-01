<div class="form theme-form">
    <div class="row">
        <div class="col-sm-8">
            <div class="mt-3">
                <label class="form-label">Nome / Razão Social<span> *</span></label>
                <input class="form-control" type="text" value="{{ old('name', $customer->name ?? '') }}" placeholder="Nome do cliente" name="name" id="name">
                @error('name')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="1" {{ !(old('status', $customer->status ?? 1)) == 0 ? 'selected' : '' }}>{{ __('Ativo') }}</option>
                    <option value="0" {{ (old('status', $customer->status ?? 1)) == 0 ? 'selected' : '' }}>{{ __('Inativo') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Tipo de Documento</label>
                <select class="form-select" name="document_type" id="document_type">
                    <option value="">Selecione</option>
                    <option value="cpf" {{ old('document_type', $customer->document_type ?? '') == 'cpf' ? 'selected' : '' }}>CPF</option>
                    <option value="cnpj" {{ old('document_type', $customer->document_type ?? '') == 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                </select>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Número do Documento</label>
                <input class="form-control" type="text" value="{{ old('document_number', $customer->document_number ?? '') }}" name="document_number" id="document_number">
                <small class="text-muted" id="document-status"></small>
                @error('document_number')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Telefone</label>
                <input class="form-control" type="text" value="{{ old('phone', $customer->phone ?? '') }}" name="phone" id="phone">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" value="{{ old('email', $customer->email ?? '') }}" name="email" id="email">
                @error('email')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">CEP</label>
                <input class="form-control" type="text" value="{{ old('postal_code', $customer->postal_code ?? '') }}" name="postal_code" id="postal_code" placeholder="00000-000" maxlength="9">
                <small class="text-muted" id="cep-status"></small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8">
            <div class="mt-3">
                <label class="form-label">Endereço</label>
                <input class="form-control" type="text" value="{{ old('address', $customer->address ?? '') }}" name="address" id="address">
            </div>
        </div>
        <div class="col-sm-2">
            <div class="mt-3">
                <label class="form-label">Número</label>
                <input class="form-control" type="text" value="{{ old('number', $customer->number ?? '') }}" name="number" id="number" {{ old('no_number', $customer->no_number ?? false) ? 'disabled' : '' }}>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="mt-3">
                <label class="form-label d-block">&nbsp;</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="no_number" id="no_number" value="1" {{ old('no_number', $customer->no_number ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="no_number">Sem número</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Complemento</label>
                <input class="form-control" type="text" value="{{ old('complement', $customer->complement ?? '') }}" name="complement" id="complement" placeholder="Bloco, apto, sala...">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Bairro</label>
                <input class="form-control" type="text" value="{{ old('neighborhood', $customer->neighborhood ?? '') }}" name="neighborhood" id="neighborhood">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Cidade</label>
                <input class="form-control" type="text" value="{{ old('city', $customer->city ?? '') }}" name="city" id="city">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Código IBGE do Município</label>
                <input class="form-control" type="text" value="{{ old('nfse_municipio_codigo', $customer->nfse_municipio_codigo ?? '') }}" name="nfse_municipio_codigo" id="nfse_municipio_codigo" placeholder="Ex: 3505708">
                <small class="text-muted">Necessário pra enviar o endereço na Nota Fiscal (NFS-e). <a href="https://www.ibge.gov.br/explica/codigos-dos-municipios.php" target="_blank" rel="noopener">Consultar no IBGE</a>.</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label>País</label>
                <select class="form-select" id="country" name="country_id">
                    <option value="">Selecione o país</option>
                    @foreach (\App\Models\Country::orderBy('name')->get(['id', 'name']) as $country)
                        <option value="{{ $country->id }}" {{ old('country_id', $customer->country_id ?? '') == $country->id ? 'selected' : '' }}>
                            {{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label>Estado</label>
                <input class="form-control" type="text" value="{{ old('state', $customer->state ?? '') }}" name="state" id="state">
            </div>
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Observações</label>
        <textarea class="form-control" name="notes">{{ old('notes', $customer->notes ?? '') }}</textarea>
    </div>

    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
    </div>
</div>
