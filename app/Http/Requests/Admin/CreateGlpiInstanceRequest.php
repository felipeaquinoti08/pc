<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateGlpiInstanceRequest extends FormRequest
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
            'oauth_client_secret' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'entities_id' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:0,1'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
