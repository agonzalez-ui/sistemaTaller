<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasModulePermission('inventory', 'edit') ?? false) && ($this->input('type') !== 'ADJUST' || $this->user()->isAdministrator());
    }

    public function rules(): array
    {
        return ['type' => ['required', Rule::in(['IN', 'OUT', 'ADJUST'])], 'quantity' => ['required', 'integer', 'min:'.($this->input('type') === 'ADJUST' ? 0 : 1), 'max:1000000'], 'notes' => ['required', 'string', 'max:250'], 'request_token' => ['required', 'uuid']];
    }

    public function attributes(): array
    {
        return ['quantity' => 'cantidad', 'notes' => 'motivo', 'type' => 'tipo de movimiento'];
    }
}
