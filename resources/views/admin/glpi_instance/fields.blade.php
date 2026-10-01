<div class="form theme-form">
    @isset($glpiInstance)
        <div class="mt-3">
            <form action="{{ route('admin.glpi_instance.test-connection', $glpiInstance->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">Testar Conexão</button>
            </form>
        </div>
    @endisset

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Nome<span> *</span></label>
                <input class="form-control" type="text" value="{{ old('name', $glpiInstance->name ?? '') }}" placeholder="Ex: GLPI Suporte CCITI" name="name">
                @error('name')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">URL Base<span> *</span></label>
                <input class="form-control" type="text" value="{{ old('base_url', $glpiInstance->base_url ?? '') }}" placeholder="https://suporte.cciti.com.br" name="base_url">
                <small class="text-muted">Sem barra no final. A API v2 é acessada em &lt;URL&gt;/api.php/v2/...</small>
                @error('base_url')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Client ID (OAuth)<span> *</span></label>
                <input class="form-control" type="text" value="{{ old('oauth_client_id', $glpiInstance->oauth_client_id ?? '') }}" name="oauth_client_id">
                <small class="text-muted">Gerado em Configurar &gt; API &gt; Clientes OAuth, dentro do GLPI.</small>
                @error('oauth_client_id')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Client Secret (OAuth)@if(isset($glpiInstance))<small class="text-muted"> (deixe em branco pra manter)</small>@else<span> *</span>@endif</label>
                <input class="form-control" type="password" value="" placeholder="{{ isset($glpiInstance) ? '••••••••' : '' }}" name="oauth_client_secret">
                @error('oauth_client_secret')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Usuário Técnico (GLPI)<span> *</span></label>
                <input class="form-control" type="text" value="{{ old('username', $glpiInstance->username ?? '') }}" placeholder="painel.central" name="username">
                <small class="text-muted">Conta técnica no GLPI usada pelo grant OAuth2 "password".</small>
                @error('username')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Senha do Usuário Técnico @if(isset($glpiInstance))<small class="text-muted"> (deixe em branco pra manter)</small>@else<span> *</span>@endif</label>
                <input class="form-control" type="password" value="" placeholder="{{ isset($glpiInstance) ? '••••••••' : '' }}" name="password">
                @error('password')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Entidade GLPI (entities_id)</label>
                <input class="form-control" type="number" min="0" value="{{ old('entities_id', $glpiInstance->entities_id ?? 0) }}" name="entities_id">
                <small class="text-muted">0 = entidade raiz.</small>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="1" {{ (old('status', $glpiInstance->status ?? 1)) == 1 ? 'selected' : '' }}>{{ __('Ativa') }}</option>
                    <option value="0" {{ (old('status', $glpiInstance->status ?? 1)) == 0 ? 'selected' : '' }}>{{ __('Inativa') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label">Observações</label>
        <textarea class="form-control" name="notes">{{ old('notes', $glpiInstance->notes ?? '') }}</textarea>
    </div>

    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
    </div>
</div>
