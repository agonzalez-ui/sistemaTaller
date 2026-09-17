<?php

namespace App\Http\Requests;

use App\Models\SparePartBrand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveSparePartBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => preg_replace('/\s+/u', ' ', trim($this->input('name')))]);
        }
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:80', Rule::unique('spare_part_brands')->ignore($this->route('spare_part_brand'))], 'active' => ['required', 'boolean']];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->has('name')) {
                return;
            }
            $brand = $this->route('spare_part_brand');
            $duplicate = SparePartBrand::when($brand, fn ($q) => $q->where('id', '!=', $brand->id))->get(['name'])->contains(fn ($existing) => mb_strtolower(trim($existing->name)) === mb_strtolower($this->input('name')));
            if ($duplicate) {
                $validator->errors()->add('name', 'Esta marca ya existe. Si está inactiva, puede reactivarla desde la lista.');
            }
        }];
    }

    public function attributes(): array
    {
        return ['name' => 'nombre de la marca', 'active' => 'estado'];
    }

    public function messages(): array
    {
        return ['name.unique' => 'Esta marca ya existe. Si está inactiva, puede reactivarla desde la lista.'];
    }
}
