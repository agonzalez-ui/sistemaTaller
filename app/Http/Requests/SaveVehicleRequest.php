<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasModulePermission('vehicles', $this->route('vehicle') ? 'edit' : 'create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('license_plate'))) {
            $this->merge(['license_plate' => strtoupper(trim($this->input('license_plate')))]);
        }
    }

    public function rules(): array
    {
        return [
            'license_plate' => ['required', 'string', 'max:15', 'regex:/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/', Rule::unique('vehicles')->ignore($this->route('vehicle'))],
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('active', true)],
            'vehicle_brand_id' => ['required', 'integer', Rule::exists('vehicle_brands', 'id')->where(fn ($q) => $q->where('active', true)->when($this->route('vehicle'), fn ($q, $vehicle) => $q->orWhere('id', $vehicle->vehicle_brand_id)))],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'color' => ['nullable', 'string', 'max:40'],
            'active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['license_plate.unique' => 'Ya existe una moto con esa placa, incluso si está inactiva.', 'license_plate.regex' => 'La placa debe contener letras, números y, si corresponde, guiones.', 'customer_id.exists' => 'Seleccione un cliente activo.', 'vehicle_brand_id.exists' => 'Seleccione una marca activa.'];
    }

    public function attributes(): array
    {
        return ['license_plate' => 'placa', 'customer_id' => 'cliente', 'vehicle_brand_id' => 'marca', 'model' => 'modelo', 'year' => 'año', 'color' => 'color', 'active' => 'estado'];
    }
}
