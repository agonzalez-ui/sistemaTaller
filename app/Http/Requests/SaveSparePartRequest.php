<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSparePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasModulePermission('inventory', $this->route('sparepart') ? 'edit' : 'create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }

    public function rules(): array
    {
        return ['code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9]+(?:[-_.][A-Z0-9]+)*$/', Rule::unique('spare_parts')->ignore($this->route('sparepart'))],
            'name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:250'],
            'spare_part_brand_id' => ['nullable', 'integer', Rule::exists('spare_part_brands', 'id')->where(fn ($q) => $q->where('active', true)->when($this->route('sparepart'), fn ($q, $part) => $q->orWhere('id', $part->spare_part_brand_id)))],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'minimum_quantity' => ['required', 'integer', 'min:0', 'max:1000000'], 'active' => ['required', 'boolean']];
    }

    public function messages(): array
    {
        return ['code.unique' => 'Este código ya existe, incluso si el repuesto está inactivo.', 'code.regex' => 'Use letras, números, guiones, puntos o guiones bajos en el código.', 'spare_part_brand_id.exists' => 'Seleccione una marca activa.'];
    }

    public function attributes(): array
    {
        return ['code' => 'código', 'name' => 'nombre', 'description' => 'descripción', 'price' => 'precio', 'minimum_quantity' => 'existencia mínima', 'active' => 'estado', 'spare_part_brand_id' => 'marca'];
    }
}
