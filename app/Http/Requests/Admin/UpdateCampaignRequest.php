<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('campaigns', 'slug')->ignore($this->route('campaign'))],
            'service_id' => ['required', 'exists:services,id'],
            'description' => ['nullable', 'string'],
            'trial_days' => ['required', 'integer', 'min:1', 'max:365'],
            'quantity' => ['required', 'integer', 'min:1'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
