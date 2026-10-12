{{-- Included from create.blade.php / edit.blade.php inside @section('scripts')
     - see admin/customer/scripts.blade.php for why this can't live inline in
     fields.blade.php (main_content renders before the page's scripts). --}}
<script>
    document.querySelectorAll('.service-plan-toggle').forEach(function (checkbox) {
        var priceInput = document.getElementById(checkbox.dataset.for);

        checkbox.addEventListener('change', function () {
            priceInput.disabled = !checkbox.checked;
            if (checkbox.checked && !priceInput.value) {
                priceInput.focus();
            }
            if (!checkbox.checked) {
                priceInput.value = '';
            }
        });
    });
</script>
