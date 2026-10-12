<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('services', 'slug')->ignore($this->route('service'))],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_type' => ['required', 'in:one_time,monthly,yearly'],
            'status' => ['nullable', 'in:0,1'],
            'pricing_plans' => ['nullable', 'array'],
            'pricing_plans.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
