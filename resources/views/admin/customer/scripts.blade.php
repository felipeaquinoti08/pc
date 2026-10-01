{{-- Included from create.blade.php / edit.blade.php inside @section('scripts'),
     which - unlike @include'ing this straight into main_content - renders
     after jquery.min.js loads at the bottom of layouts/simple/scripts.blade.php.
     A <script> using $ inside fields.blade.php itself would run too early
     ("$ is not defined") since fields.blade.php is included from
     main_content, above the page's scripts. --}}
<script>
    $(document).ready(function () {
        var brazilCountryId = '{{ \App\Models\Country::where('iso_3166_2', 'BR')->value('id') }}';

        // "Sem número": disables and clears the Número field instead of
        // just hiding the checkbox's intent - avoids submitting a stale
        // number alongside "no number".
        function toggleNumberField() {
            var noNumber = $('#no_number').is(':checked');
            $('#number').prop('disabled', noNumber);
            if (noNumber) {
                $('#number').val('');
            }
        }
        $('#no_number').on('change', toggleNumberField);
        toggleNumberField();

        // CEP lookup (ViaCEP - free, no API key, CORS-enabled): fills
        // street, neighborhood, city, country and state automatically from
        // the postal code. Only ever assists - never blocks saving if the
        // lookup fails or the CEP isn't found, and anything it fills stays
        // editable (Número/Complemento are never touched - ViaCEP doesn't
        // know them).
        $('#postal_code').on('blur', function () {
            var cep = $(this).val().replace(/\D/g, '');
            var $status = $('#cep-status');
            $status.text('');

            if (cep.length !== 8) {
                return;
            }

            $status.text('Buscando endereço...');

            $.ajax({
                url: 'https://viacep.com.br/ws/' + cep + '/json/',
                type: 'GET',
                dataType: 'json',
            }).done(function (data) {
                if (data.erro) {
                    $status.text('CEP não encontrado.');
                    return;
                }

                $status.text('');
                if (data.logradouro) $('#address').val(data.logradouro);
                if (data.bairro) $('#neighborhood').val(data.bairro);
                if (data.localidade) $('#city').val(data.localidade);
                if (data.estado) $('#state').val(data.estado);

                if (brazilCountryId && $('#country').val() != brazilCountryId) {
                    $('#country').val(brazilCountryId);
                }
            }).fail(function () {
                $status.text('Não foi possível consultar o CEP agora.');
            });
        });

        // CNPJ lookup (BrasilAPI, via nosso próprio backend - ver
        // App\Services\CnpjLookupService - pra não depender de CORS liberado
        // por eles nem expor a chamada externa direto do navegador): busca
        // razão social e endereço completo assim que um CNPJ válido é
        // digitado. Mesma regra do CEP acima - só ajuda, nunca bloqueia o
        // salvamento, e tudo que preenche continua editável.
        $('#document_number').on('blur', function () {
            var isCnpj = $('#document_type').val() === 'cnpj';
            var doc = $(this).val().replace(/\D/g, '');
            var $status = $('#document-status');
            $status.text('');

            if (!isCnpj || doc.length !== 14) {
                return;
            }

            $status.text('Consultando CNPJ...');

            $.ajax({
                url: '{{ url('admin/customer/cnpj-lookup') }}/' + doc,
                type: 'GET',
                dataType: 'json',
            }).done(function (response) {
                var data = response.data;
                $status.text('');

                if (data.name) $('#name').val(data.name);
                if (data.email) $('#email').val(data.email);
                if (data.phone) $('#phone').val(data.phone);
                if (data.postal_code) $('#postal_code').val(data.postal_code);
                if (data.address) $('#address').val(data.address);
                if (data.number) $('#number').val(data.number);
                if (data.complement) $('#complement').val(data.complement);
                if (data.neighborhood) $('#neighborhood').val(data.neighborhood);
                if (data.city) $('#city').val(data.city);
                if (data.state) $('#state').val(data.state);
                if (data.nfse_municipio_codigo) $('#nfse_municipio_codigo').val(data.nfse_municipio_codigo);

                if (brazilCountryId && $('#country').val() != brazilCountryId) {
                    $('#country').val(brazilCountryId);
                }
            }).fail(function (xhr) {
                var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Não foi possível consultar o CNPJ agora.';
                $status.text(message);
            });
        });

        // Reconsulta se o tipo virar CNPJ depois do número já preenchido
        // (ex: usuário esqueceu de trocar o tipo antes de digitar).
        $('#document_type').on('change', function () {
            $('#document_number').trigger('blur');
        });
    });
</script>
