<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSsoSettingRequest extends FormRequest
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
            'sso_tenant_id' => ['required', 'string', 'max:255'],
            'sso_client_id' => ['required', 'string', 'max:255'],
            // Blank keeps the currently saved secret (see SettingController::updateSso).
            'sso_client_secret' => ['nullable', 'string'],
        ];
    }
}
