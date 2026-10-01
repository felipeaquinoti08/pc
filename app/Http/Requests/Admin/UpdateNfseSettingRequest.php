<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNfseSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nfse_environment' => ['required', 'in:homologacao,producao'],
            'nfse_dps_serie' => ['required', 'string', 'max:20'],
            'nfse_cnpj' => ['required', 'string', 'max:20'],
            'nfse_inscricao_municipal' => ['nullable', 'string', 'max:30'],
            'nfse_op_simples_nacional' => ['required', 'integer', 'in:1,2,3'],
            'nfse_reg_esp_trib' => ['required', 'integer', 'between:0,6'],
            'nfse_telefone' => ['nullable', 'string', 'max:20'],
            'nfse_email' => ['nullable', 'email', 'max:255'],
            'nfse_municipio_codigo' => ['required', 'string', 'max:10'],
            'nfse_municipio_nome' => ['required', 'string', 'max:255'],
            'nfse_uf' => ['required', 'string', 'size:2'],
            'nfse_cep' => ['nullable', 'string', 'max:10'],
            'nfse_logradouro' => ['nullable', 'string', 'max:255'],
            'nfse_numero' => ['nullable', 'string', 'max:20'],
            'nfse_bairro' => ['nullable', 'string', 'max:255'],
            'nfse_codigo_tributacao_nacional' => ['required', 'string', 'max:20'],
            'nfse_codigo_tributacao_municipal' => ['nullable', 'string', 'max:20'],
            'nfse_codigo_nbs' => ['nullable', 'string', 'max:20'],
            'nfse_aliquota_iss' => ['required', 'numeric', 'min:0', 'max:100'],
            'nfse_aliquota_pis' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nfse_aliquota_cofins' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nfse_percentual_trib_federal' => ['nullable', 'numeric', 'min:0', 'max:100'],
            // The .pfx itself: optional on every save (keeps the one already
            // on blob storage when not re-uploading), same convention as
            // secrets elsewhere on this form (blank = keep current value).
            'nfse_certificate' => ['nullable', 'file', 'max:5120'],
            'nfse_certificate_password' => ['nullable', 'string'],
        ];
    }
}
