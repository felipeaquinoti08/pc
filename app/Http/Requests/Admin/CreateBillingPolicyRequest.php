<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBillingPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'billable_type' => ['nullable', 'in:license,service'],
            'billable_id' => ['required_with:billable_type', 'nullable', 'integer'],
            'description' => [Rule::requiredIf(fn () => blank($this->billable_type)), 'nullable', 'string', 'max:255'],
            'frequency' => ['required', 'in:once,monthly,semiannual,annual'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_day' => ['required', 'integer', 'between:1,28'],
            'starts_at' => ['required', 'date'],
            'status' => ['required', 'in:active,paused,cancelled'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
