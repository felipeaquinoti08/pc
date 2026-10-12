<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:services,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_type' => ['required', 'in:one_time,monthly,yearly'],
            'status' => ['nullable', 'in:0,1'],
            'pricing_plans' => ['nullable', 'array'],
            'pricing_plans.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
