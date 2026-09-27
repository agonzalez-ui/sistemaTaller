<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasModulePermission('billing', 'delete') ?? false;
    }

    public function rules(): array
    {
        return ['cancellation_reason' => ['required', 'string', 'min:5', 'max:250']];
    }

    public function attributes(): array
    {
        return ['cancellation_reason' => 'motivo de anulación'];
    }
}
