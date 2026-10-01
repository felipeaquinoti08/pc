@php
    $initialPhases = old('phases', $proposal->exists ? $proposal->phases->map(fn ($phase) => [
        'title' => $phase->title,
        'subtitle' => $phase->subtitle,
        'items' => $phase->items->pluck('description')->values()->all(),
    ])->values()->all() : []);

    $initialPrerequisites = old('prerequisites', $proposal->exists ? $proposal->prerequisites->pluck('description')->values()->all() : []);

    $initialScheduleSteps = old('schedule_steps', $proposal->exists ? $proposal->scheduleSteps->map(fn ($step) => [
        'duration_label' => $step->duration_label,
        'description' => $step->description,
    ])->values()->all() : []);
@endphp

<div class="form theme-form">
    <div class="col-12"><h5 class="mb-0">Dados Gerais</h5></div>
    <div class="row">
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Cliente (Contratante)<span> *</span></label>
                <select class="form-select" name="customer_id">
                    <option value="">Selecione</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $proposal->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->document_number ?: 'sem documento' }})</option>
                    @endforeach
                </select>
                @error('customer_id')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="mt-3">
                <label class="form-label">Responsável pelo Contratante</label>
                <input class="form-control" type="text" name="contratante_responsavel"
                    value="{{ old('contratante_responsavel', $proposal->contratante_responsavel ?? '') }}" placeholder="Ex: Jean Batti">
                @error('contratante_responsavel')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            <div class="mt-3">
                <label class="form-label">Título da Proposta<span> *</span></label>
                <input class="form-control" type="text" name="title"
                    value="{{ old('title', $proposal->title ?? '') }}" placeholder="Ex: Implantação do Sistema ITSM GLPI">
                @error('title')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-5">
            <div class="mt-3">
                <label class="form-label">Subtítulo</label>
                <input class="form-control" type="text" name="subtitle"
                    value="{{ old('subtitle', $proposal->subtitle ?? '') }}" placeholder="Ex: Infraestrutura, integração e operação consultiva">
                @error('subtitle')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-2">
            <div class="mt-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    @foreach (\App\Models\Proposal::statuses() as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $proposal->status ?? 'draft') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8">
            <div class="mt-3">
                <label class="form-label">Rótulo do documento (cabeçalho/rodapé)<span> *</span></label>
                <input class="form-control" type="text" name="document_label"
                    value="{{ old('document_label', $proposal->document_label ?? '') }}" placeholder="Ex: Implantação GLPI">
                <small class="text-muted">Aparece como "Proposta Comercial · [rótulo] · [ano]" no topo de cada página.</small>
                @error('document_label')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-4">
            <div class="mt-3">
                <label class="form-label">Ano<span> *</span></label>
                <input class="form-control" type="number" name="proposal_year"
                    value="{{ old('proposal_year', $proposal->proposal_year ?? '') }}" placeholder="{{ $placeholders['proposal_year'] }}">
                @error('proposal_year')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="mt-3">
                <label class="form-label">Sobre este documento</label>
                <textarea class="form-control" name="about_text" rows="3" placeholder="{{ $placeholders['about_text'] }}">{{ old('about_text', $proposal->about_text ?? '') }}</textarea>
                @error('about_text')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12 mt-4"><h5 class="mb-0">Valores e Condições de Pagamento</h5></div>
    <div class="row">
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Valor Total (R$)<span> *</span></label>
                <input class="form-control" type="number" step="0.01" min="0" name="total_value"
                    value="{{ old('total_value', $proposal->total_value ?? '') }}">
                @error('total_value')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Prazo Estimado</label>
                <input class="form-control" type="text" name="estimated_days"
                    value="{{ old('estimated_days', $proposal->estimated_days ?? '') }}" placeholder="Ex: ~60 d.u.">
                @error('estimated_days')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Sinal de Entrada (%)<span> *</span></label>
                <input class="form-control" type="number" step="0.01" min="0" max="100" name="down_payment_percent"
                    value="{{ old('down_payment_percent', $proposal->down_payment_percent ?? '') }}" placeholder="{{ $placeholders['down_payment_percent'] }}">
                @error('down_payment_percent')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        <div class="col-sm-3">
            <div class="mt-3">
                <label class="form-label">Parcela Final (%)<span> *</span></label>
                <input class="form-control" type="number" step="0.01" min="0" max="100" name="final_payment_percent"
                    value="{{ old('final_payment_percent', $proposal->final_payment_percent ?? '') }}" placeholder="{{ $placeholders['final_payment_percent'] }}">
                @error('final_payment_percent')
                    <span class="text-danger"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-12 mt-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Fases do Projeto (Escopo)</h5>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-3">
                <textarea class="form-control" name="object_intro" rows="2" placeholder="{{ $placeholders['object_intro'] }}">{{ old('object_intro', $proposal->object_intro ?? '') }}</textarea>
                <small class="text-muted">Introdução da Cláusula Primeira (Do Objeto e Escopo), antes da lista de fases.</small>
            </div>
            <div class="mt-3" id="phases-container"></div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-phase-btn">+ Adicionar fase</button>
        </div>
    </div>

    <div class="col-12 mt-4"><h5 class="mb-0">Pré-Requisitos Técnicos Obrigatórios</h5></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-3" id="prerequisites-container"></div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-prerequisite-btn">+ Adicionar pré-requisito</button>
        </div>
    </div>

    <div class="col-12 mt-4"><h5 class="mb-0">Cronograma e Prazos</h5></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-3" id="schedule-steps-container"></div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-schedule-step-btn">+ Adicionar etapa</button>
        </div>
    </div>

    <div class="col-12 mt-4 d-flex gap-2">
        <button type="submit" name="action" value="save" class="btn btn-outline-primary">{{ __('Salvar') }}</button>
        <button type="submit" name="action" value="save_pdf" class="btn btn-primary">{{ __('Salvar e Gerar PDF') }}</button>
    </div>
</div>

<script type="application/json" id="proposal-initial-phases">{!! json_encode($initialPhases) !!}</script>
<script type="application/json" id="proposal-initial-prerequisites">{!! json_encode($initialPrerequisites) !!}</script>
<script type="application/json" id="proposal-initial-schedule-steps">{!! json_encode($initialScheduleSteps) !!}</script>
