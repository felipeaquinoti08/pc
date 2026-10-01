<script>
    $(document).on('change', '.contact-lead-status-select', function () {
        var $select = $(this);
        var badgeClasses = {
            new: 'badge-light-primary',
            in_progress: 'badge-light-warning',
            closed: 'badge-light-success'
        };

        $.ajax({
            type: 'PUT',
            url: $select.data('route'),
            data: {
                status: $select.val(),
                _token: '{{ csrf_token() }}'
            },
            success: function () {
                $select.removeClass('badge-light-primary badge-light-warning badge-light-success')
                    .addClass(badgeClasses[$select.val()]);
                toastr.success('Status atualizado com sucesso');
            },
            error: function () {
                toastr.error('Não foi possível atualizar o status');
            }
        });
    });
</script>
