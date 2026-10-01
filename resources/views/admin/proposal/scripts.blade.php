<script>
    (function () {
        function readJson(id) {
            var el = document.getElementById(id);
            return el ? JSON.parse(el.textContent || '[]') : [];
        }

        function field(tag, attrs) {
            var node = document.createElement(tag);
            Object.keys(attrs || {}).forEach(function (key) {
                node.setAttribute(key, attrs[key]);
            });
            return node;
        }

        function removeButton(label, onClick) {
            var btn = field('button', { type: 'button', class: 'btn btn-outline-danger' });
            btn.textContent = label;
            btn.addEventListener('click', onClick);
            return btn;
        }

        // ---- Fases (com itens aninhados) ----
        (function () {
            var container = document.getElementById('phases-container');
            var addBtn = document.getElementById('add-phase-btn');
            var phaseIndex = 0;

            function addPhase(data) {
                data = data || {};
                var index = phaseIndex++;

                var card = field('div', { class: 'card mb-3' });
                var body = field('div', { class: 'card-body' });
                card.appendChild(body);

                var header = field('div', { class: 'd-flex justify-content-between align-items-center mb-2' });
                var title = field('h6', { class: 'mb-0' });
                title.textContent = 'Fase';
                header.appendChild(title);
                header.appendChild(removeButton('Remover fase', function () { card.remove(); }));
                body.appendChild(header);

                var row = field('div', { class: 'row g-3' });

                var titleCol = field('div', { class: 'col-sm-4' });
                var titleLabel = field('label', { class: 'form-label' });
                titleLabel.textContent = 'Título *';
                var titleInput = field('input', { class: 'form-control', type: 'text', name: 'phases[' + index + '][title]', placeholder: 'Ex: Fase 01' });
                titleInput.value = data.title || '';
                titleCol.appendChild(titleLabel);
                titleCol.appendChild(titleInput);

                var subtitleCol = field('div', { class: 'col-sm-8' });
                var subtitleLabel = field('label', { class: 'form-label' });
                subtitleLabel.textContent = 'Subtítulo';
                var subtitleInput = field('input', { class: 'form-control', type: 'text', name: 'phases[' + index + '][subtitle]', placeholder: 'Ex: Infraestrutura, Instalação e Core do Sistema' });
                subtitleInput.value = data.subtitle || '';
                subtitleCol.appendChild(subtitleLabel);
                subtitleCol.appendChild(subtitleInput);

                row.appendChild(titleCol);
                row.appendChild(subtitleCol);
                body.appendChild(row);

                var itemsWrap = field('div', { class: 'mt-3' });
                var itemsLabel = field('label', { class: 'form-label' });
                itemsLabel.textContent = 'Itens do escopo';
                var itemsContainer = field('div', {});
                var addItemBtn = field('button', { type: 'button', class: 'btn btn-sm btn-outline-secondary mt-2' });
                addItemBtn.textContent = '+ Adicionar item';

                var itemIndex = 0;
                function addItem(description) {
                    var itemIdx = itemIndex++;
                    var group = field('div', { class: 'input-group mb-2' });
                    var input = field('input', { class: 'form-control', type: 'text', name: 'phases[' + index + '][items][' + itemIdx + ']', placeholder: 'Ex: Provisionamento do ambiente via Docker.' });
                    input.value = description || '';
                    group.appendChild(input);
                    group.appendChild(removeButton('×', function () { group.remove(); }));
                    itemsContainer.appendChild(group);
                }

                addItemBtn.addEventListener('click', function () { addItem(''); });

                (data.items || []).forEach(addItem);
                if (!data.items || !data.items.length) {
                    addItem('');
                }

                itemsWrap.appendChild(itemsLabel);
                itemsWrap.appendChild(itemsContainer);
                itemsWrap.appendChild(addItemBtn);
                body.appendChild(itemsWrap);

                container.appendChild(card);
            }

            addBtn.addEventListener('click', function () { addPhase({}); });

            var initial = readJson('proposal-initial-phases');
            if (initial.length) {
                initial.forEach(addPhase);
            }
        })();

        // ---- Pré-requisitos (lista simples) ----
        (function () {
            var container = document.getElementById('prerequisites-container');
            var addBtn = document.getElementById('add-prerequisite-btn');
            var index = 0;

            function addPrerequisite(description) {
                var idx = index++;
                var group = field('div', { class: 'input-group mb-2' });
                var input = field('input', { class: 'form-control', type: 'text', name: 'prerequisites[' + idx + ']', placeholder: 'Ex: Servidor dedicado Linux Ubuntu (última versão estável LTS).' });
                input.value = description || '';
                group.appendChild(input);
                group.appendChild(removeButton('×', function () { group.remove(); }));
                container.appendChild(group);
            }

            addBtn.addEventListener('click', function () { addPrerequisite(''); });

            var initial = readJson('proposal-initial-prerequisites');
            initial.forEach(addPrerequisite);
        })();

        // ---- Cronograma (rótulo de duração + descrição) ----
        (function () {
            var container = document.getElementById('schedule-steps-container');
            var addBtn = document.getElementById('add-schedule-step-btn');
            var index = 0;

            function addStep(data) {
                data = data || {};
                var idx = index++;
                var row = field('div', { class: 'row g-2 mb-2 align-items-center' });

                var durationCol = field('div', { class: 'col-sm-3' });
                var durationInput = field('input', { class: 'form-control', type: 'text', name: 'schedule_steps[' + idx + '][duration_label]', placeholder: 'Ex: 20 (vinte) dias úteis' });
                durationInput.value = data.duration_label || '';
                durationCol.appendChild(durationInput);

                var descCol = field('div', { class: 'col-sm-8' });
                var descInput = field('input', { class: 'form-control', type: 'text', name: 'schedule_steps[' + idx + '][description]', placeholder: 'Ex: Destinados à instalação do ambiente core do sistema.' });
                descInput.value = data.description || '';
                descCol.appendChild(descInput);

                var btnCol = field('div', { class: 'col-sm-1' });
                var row_ = row;
                btnCol.appendChild(removeButton('×', function () { row_.remove(); }));

                row.appendChild(durationCol);
                row.appendChild(descCol);
                row.appendChild(btnCol);

                container.appendChild(row);
            }

            addBtn.addEventListener('click', function () { addStep({}); });

            var initial = readJson('proposal-initial-schedule-steps');
            initial.forEach(addStep);
        })();
    })();
</script>
