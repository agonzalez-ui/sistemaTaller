<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasModulePermission('orders', 'edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'spare_part_id' => ['required', 'integer', Rule::exists('spare_parts', 'id')->where('active', true)],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }

    public function attributes(): array
    {
        return ['spare_part_id' => 'repuesto', 'quantity' => 'cantidad'];
    }
}
