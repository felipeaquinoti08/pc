<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['nullable', 'in:cpf,cnpj'],
            'document_number' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'no_number' => ['nullable', 'boolean'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'nfse_municipio_codigo' => ['nullable', 'string', 'max:10'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'state' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:0,1'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
