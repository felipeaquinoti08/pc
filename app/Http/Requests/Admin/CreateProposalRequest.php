<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'document_label' => ['required', 'string', 'max:255'],
            'proposal_year' => ['required', 'integer', 'digits:4'],
            'about_text' => ['nullable', 'string'],

            'total_value' => ['required', 'numeric', 'min:0'],
            'down_payment_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'final_payment_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'estimated_days' => ['nullable', 'string', 'max:255'],

            'contratante_responsavel' => ['nullable', 'string', 'max:255'],

            'object_intro' => ['nullable', 'string'],

            'status' => ['required', 'in:draft,sent,accepted,rejected'],

            'phases' => ['nullable', 'array'],
            'phases.*.title' => ['required_with:phases', 'string', 'max:255'],
            'phases.*.subtitle' => ['nullable', 'string', 'max:255'],
            'phases.*.items' => ['nullable', 'array'],
            'phases.*.items.*' => ['nullable', 'string', 'max:500'],

            'prerequisites' => ['nullable', 'array'],
            'prerequisites.*' => ['nullable', 'string', 'max:500'],

            'schedule_steps' => ['nullable', 'array'],
            'schedule_steps.*.duration_label' => ['nullable', 'string', 'max:255'],
            'schedule_steps.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
