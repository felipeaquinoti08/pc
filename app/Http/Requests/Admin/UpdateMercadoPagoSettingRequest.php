<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMercadoPagoSettingRequest extends FormRequest
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
            'mp_environment' => ['required', 'in:test,production'],
            'mp_public_key' => ['nullable', 'string', 'max:255'],
            'mp_test_public_key' => ['nullable', 'string', 'max:255'],
            // Blank keeps the currently saved value (see SettingController::updateMercadoPago).
            'mp_access_token' => ['nullable', 'string'],
            'mp_webhook_secret' => ['nullable', 'string'],
            'mp_test_access_token' => ['nullable', 'string'],
            'mp_test_webhook_secret' => ['nullable', 'string'],
        ];
    }
}
