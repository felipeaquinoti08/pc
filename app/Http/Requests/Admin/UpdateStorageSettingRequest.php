<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStorageSettingRequest extends FormRequest
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
            'blob_account_name' => ['required', 'string', 'max:255'],
            // Blank keeps the currently saved key (see SettingController::updateStorage).
            'blob_account_key' => ['nullable', 'string'],
            'blob_container' => ['required', 'string', 'max:255'],
            'blob_endpoint_suffix' => ['nullable', 'string', 'max:255'],
        ];
    }
}
