<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasModulePermission('billing', 'create') ?? false;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'discount' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
        ];
    }

    public function attributes(): array
    {
        return ['order_id' => 'orden', 'discount' => 'descuento', 'tax_rate' => 'porcentaje de impuesto'];
    }
}
