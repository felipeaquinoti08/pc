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
