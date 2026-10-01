<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = auth()->user()->id;
        return [
            'email' => ['required', 'email', 'unique:users,email,'.$id.',id'],
            'first_name' => ['required', 'string','max:255'],
            'last_name' => ['required', 'string','max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'no_number' => ['nullable', 'boolean'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country_code' => ['numeric'],
            'phone' => ['required', 'numeric', 'digits_between:6,15','unique:users,phone,'.$id.',id'],
            'bio' => ['required','max:255'],
            // Both empty = keep current password. Only validated/applied
            // when actually filled in (see UserController::updateProfile).
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
