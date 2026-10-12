{{-- Included from create.blade.php / edit.blade.php inside @section('scripts')
     - see admin/customer/scripts.blade.php for why this can't live inline in
     fields.blade.php (main_content renders before the page's scripts). --}}
<script>
    (function () {
        var typeSelect = document.getElementById('billing-policy-billable-type');
        var fields = document.querySelectorAll('.billing-policy-billable-field');

        function sync() {
            var selected = typeSelect.value;

            fields.forEach(function (field) {
                var isMatch = field.dataset.for === selected;
                field.style.display = isMatch ? '' : 'none';

                var input = field.querySelector('select, input');
                if (input) {
                    input.disabled = !isMatch;
                }
            });
        }

        typeSelect.addEventListener('change', sync);
        sync();
    })();

    (function () {
        var serviceSelect = document.getElementById('billing-policy-service-id');
        var planSelect = document.getElementById('billing-policy-service-plan');
        var frequencySelect = document.getElementById('billing-policy-frequency');
        var amountInput = document.getElementById('billing-policy-amount');
        var plansByService = JSON.parse(document.getElementById('billing-policy-service-plans').textContent || '{}');

        if (!serviceSelect || !planSelect) {
            return;
        }

        function renderPlanOptions() {
            var plans = plansByService[serviceSelect.value] || [];

            planSelect.innerHTML = '<option value="">Preencher frequência/valor manualmente</option>';
            plans.forEach(function (plan, index) {
                var option = document.createElement('option');
                option.value = index;
                option.textContent = plan.label + ' - R$ ' + plan.price.replace('.', ',');
                planSelect.appendChild(option);
            });
            planSelect.disabled = plans.length === 0;
        }

        serviceSelect.addEventListener('change', renderPlanOptions);

        planSelect.addEventListener('change', function () {
            var plans = plansByService[serviceSelect.value] || [];
            var plan = plans[planSelect.value];

            if (!plan) {
                return;
            }

            frequencySelect.value = plan.frequency;
            amountInput.value = plan.price;
        });

        renderPlanOptions();
    })();

    (function () {
        var textarea = document.getElementById('billing-policy-notes');

        document.querySelectorAll('[data-insert-variable]').forEach(function (button) {
            button.addEventListener('click', function () {
                var token = button.dataset.insertVariable;
                var start = textarea.selectionStart;
                var end = textarea.selectionEnd;
                var value = textarea.value;

                textarea.value = value.slice(0, start) + token + value.slice(end);
                textarea.focus();
                textarea.selectionStart = textarea.selectionEnd = start + token.length;
            });
        });
    })();
</script>
