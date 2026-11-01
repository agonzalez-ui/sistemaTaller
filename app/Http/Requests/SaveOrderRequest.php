<?php

namespace App\Http\Requests;

use App\Models\OrderStatus;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order
            ? ($this->user()?->canEditOrder($order) ?? false)
            : ($this->user()?->hasModulePermission('orders', 'create') ?? false);
    }

    protected function prepareForValidation(): void
    {
        $order = $this->route('order');
        if ($order && $this->user()?->isMechanic()) {
            $this->merge([
                'customer_id' => $order->customer_id,
                'vehicle_id' => $order->vehicle_id,
                'mechanic_id' => $order->mechanic_id,
                'description' => $order->description,
                'odometer' => $order->odometer,
                'fuel_level' => $order->fuel_level,
                'intake_condition' => $order->intake_condition,
                'intake_accessories' => $order->intake_accessories ?? [],
                'labor_cost' => $order->labor_cost,
                'received_at' => $order->received_at?->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('active', true)],
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')->where('active', true)],
            'order_status_id' => ['required', 'integer', Rule::exists('order_statuses', 'id')->where('active', true)],
            'mechanic_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('active', true))],
            'description' => ['required', 'string', 'max:500'],
            'diagnosis' => ['nullable', 'string', 'max:500'],
            'odometer' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'fuel_level' => ['nullable', Rule::in(['empty', 'quarter', 'half', 'three_quarters', 'full'])],
            'intake_condition' => ['nullable', 'string', 'max:2000'],
            'intake_accessories' => ['nullable', 'array'],
            'intake_accessories.*' => ['string', Rule::in(['ignition_key', 'spare_key', 'helmet', 'documents', 'top_case', 'tools', 'other'])],
            'intake_photos' => ['nullable', 'array', 'max:5'],
            'intake_photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'labor_cost' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'received_at' => ['required', 'date'],
            'status_notes' => ['nullable', 'string', 'max:250'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (! $validator->errors()->hasAny(['customer_id', 'vehicle_id'])) {
                $vehicle = Vehicle::find($this->integer('vehicle_id'));
                if (! $vehicle || $vehicle->customer_id !== $this->integer('customer_id') || $vehicle->type?->name !== 'Motocicleta') {
                    $validator->errors()->add('vehicle_id', 'Seleccione una moto activa que pertenezca al cliente.');
                }
            }
            if ($this->filled('mechanic_id')) {
                $mechanic = User::with('role')->find($this->integer('mechanic_id'));
                if (! $mechanic || ! $mechanic->active || ! $mechanic->role?->active || $mechanic->role?->name !== 'Mecánico') {
                    $validator->errors()->add('mechanic_id', 'Seleccione un usuario activo con rol Mecánico.');
                }
            }
            $order = $this->route('order');
            if ($order && $order->order_status_id !== $this->integer('order_status_id') && ! $this->filled('status_notes')) {
                $validator->errors()->add('status_notes', 'Indique el motivo o detalle del cambio de estado.');
            }
            $requestedStatus = OrderStatus::find($this->integer('order_status_id'));
            if (! $order && $requestedStatus?->name !== 'Recibido') {
                $validator->errors()->add('order_status_id', 'Una orden nueva debe iniciar en estado Recibido.');
            }
            if ($requestedStatus?->name === 'Cancelada') {
                $validator->errors()->add('order_status_id', 'Utilice la acción Cancelar orden para devolver correctamente los repuestos.');
            }
            if ($requestedStatus?->allows_invoicing) {
                if (! $this->filled('mechanic_id')) {
                    $validator->errors()->add('mechanic_id', 'Asigne un mecánico antes de marcar la orden como lista o entregada.');
                }
                if (! $this->filled('diagnosis') || trim((string) $this->input('diagnosis')) === '') {
                    $validator->errors()->add('diagnosis', 'Registre el diagnóstico y trabajo realizado antes de finalizar la orden.');
                }
            }
            $existingPhotos = $order?->intakePhotos()->count() ?? 0;
            if ($order && $this->user()?->isMechanic() && count($this->file('intake_photos', [])) > 0) {
                $validator->errors()->add('intake_photos', 'Las fotografías de recepción solo puede administrarlas recepción o administración.');
            }
            if ($existingPhotos + count($this->file('intake_photos', [])) > 5) {
                $validator->errors()->add('intake_photos', 'La orden puede conservar un máximo de cinco fotografías de recepción.');
            }
        }];
    }

    public function attributes(): array
    {
        return ['customer_id' => 'cliente', 'vehicle_id' => 'moto', 'order_status_id' => 'estado', 'mechanic_id' => 'mecánico', 'description' => 'trabajo solicitado', 'diagnosis' => 'diagnóstico', 'odometer' => 'kilometraje', 'fuel_level' => 'nivel de combustible', 'intake_condition' => 'condición de ingreso', 'intake_accessories' => 'accesorios recibidos', 'intake_photos' => 'fotografías de recepción', 'labor_cost' => 'mano de obra', 'received_at' => 'fecha de recepción', 'status_notes' => 'detalle del cambio'];
    }
}
