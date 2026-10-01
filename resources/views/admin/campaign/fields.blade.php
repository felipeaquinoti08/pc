<div class="form theme-form">
    @isset($campaign)
        <div class="mt-3">
            <label class="form-label">Link público</label>
            <input class="form-control" type="text" value="{{ route('campaign.show', $campaign->slug) }}" readonly disabled>
            <small class="text-muted">{{ $campaign->licenses_count ?? $campaign->licenses()->count() }} licença(s) de teste emitida(s) por esta campanha.</small>
        </div>
    @endisset

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Nome<span> *</span></label>
                <input class="form-control" type="text" value="{{ old('name', $campaign->name ?? '') }}" placeholder="Ex: Teste Grátis - Entrasso" name="name">
                @error('name')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Slug (URL)@isset($campaign)<span> *</span>@endisset</label>
                <input class="form-control" type="text" value="{{ old('slug', $campaign->slug ?? '') }}" placeholder="gerado automaticamente se vazio" name="slug">
                <small class="text-muted">Usado na URL pública: /campanha/&lt;slug&gt;</small>
                @error('slug')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Serviço<span> *</span></label>
                <select class="form-select" name="service_id">
                    <option value="">Selecione</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id', $campaign->service_id ?? '') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}</option>
                    @endforeach
                </select>
                @error('service_id')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Duração do teste (dias)<span> *</span></label>
                <input class="form-control" type="number" min="1" max="365" value="{{ old('trial_days', $campaign->trial_days ?? 15) }}" name="trial_days">
                @error('trial_days')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Qtd. Instalações<span> *</span></label>
                <input class="form-control" type="number" min="1" value="{{ old('quantity', $campaign->quantity ?? 1) }}" name="quantity">
                @error('quantity')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Descrição (exibida na página de cadastro)</label>
        <textarea class="form-control" name="description" rows="3">{{ old('description', $campaign->description ?? '') }}</textarea>
        @error('description')
            <span class="text-danger"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Link do repositório para download<span> *</span></label>
                <input class="form-control" type="text" value="{{ old('repository_url', $campaign->repository_url ?? '') }}" placeholder="https://github.com/..." name="repository_url">
                @error('repository_url')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">WhatsApp para suporte</label>
                <input class="form-control" type="text" value="{{ old('whatsapp_number', $campaign->whatsapp_number ?? '') }}" placeholder="61993049590" name="whatsapp_number">
                <small class="text-muted">Vazio = usa o WhatsApp padrão ({{ \App\Models\Campaign::DEFAULT_WHATSAPP }}).</small>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Instruções adicionais (exibidas junto com a licença emitida)</label>
        <textarea class="form-control" name="instructions" rows="3">{{ old('instructions', $campaign->instructions ?? '') }}</textarea>
        @error('instructions')
            <span class="text-danger"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="1" {{ (old('status', $campaign->status ?? 1)) == 1 ? 'selected' : '' }}>{{ __('Aceitando cadastros') }}</option>
                    <option value="0" {{ (old('status', $campaign->status ?? 1)) == 0 ? 'selected' : '' }}>{{ __('Encerrada') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
    </div>
</div>
