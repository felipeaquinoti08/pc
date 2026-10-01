<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGlpiInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:255'],
            'oauth_client_id' => ['required', 'string', 'max:255'],
            // Em branco = mantém o valor já salvo (não obriga redigitar o
            // segredo toda vez que só se edita o nome/status).
            'oauth_client_secret' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'entities_id' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:0,1'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
