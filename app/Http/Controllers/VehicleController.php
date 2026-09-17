<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveVehicleRequest;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleType;
use App\Support\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:active,inactive']]);
        $vehicles = Vehicle::with(['customer', 'brand', 'type'])->withCount('orders')
            ->where('vehicle_type_id', $this->motorcycleTypeId())
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('license_plate', 'like', "%$search%")
                ->orWhere('model', 'like', "%$search%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%$search%"))
                ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', "%$search%"))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('active', $status === 'active'))
            ->orderBy('license_plate')->paginate(12)->withQueryString();

        return view('vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('vehicles.create', $this->formData());
    }

    public function store(SaveVehicleRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $vehicle = Vehicle::create([...$request->validated(), 'vehicle_type_id' => $this->motorcycleTypeId(), 'created_by' => $request->user()->id]);
            SecurityAudit::record($request, 'INSERT', 'vehicles', $vehicle->id, 'Moto registrada: '.$vehicle->license_plate.'.');
        });

        return redirect()->route('vehicles.index')->with('success', 'Moto registrada correctamente.');
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->assertMotorcycle($vehicle);

        return view('vehicles.edit', [...$this->formData($vehicle), 'vehicle' => $vehicle]);
    }

    public function update(SaveVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->assertMotorcycle($vehicle);
        DB::transaction(function () use ($request, $vehicle) {
            $vehicle->update($request->validated());
            SecurityAudit::record($request, 'UPDATE', 'vehicles', $vehicle->id, 'Datos o estado de la moto actualizados: '.$vehicle->license_plate.'.');
        });

        return redirect()->route('vehicles.index')->with('success', 'Moto actualizada correctamente.');
    }

    public function destroy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->assertMotorcycle($vehicle);
        DB::transaction(function () use ($request, $vehicle) {
            $vehicle->update(['active' => false]);
            SecurityAudit::record($request, 'DISABLE', 'vehicles', $vehicle->id, 'Moto desactivada; se conserva su historial.');
        });

        return redirect()->route('vehicles.index')->with('success', 'Moto desactivada correctamente. Se conserva su historial.');
    }

    private function motorcycleTypeId(): int
    {
        return VehicleType::where('name', 'Motocicleta')->where('active', true)->firstOrFail()->id;
    }

    private function assertMotorcycle(Vehicle $vehicle): void
    {
        abort_unless($vehicle->vehicle_type_id === $this->motorcycleTypeId(), 404);
    }

    private function formData(?Vehicle $vehicle = null): array
    {
        return ['customers' => Customer::where(fn ($q) => $q->where('active', true)->when($vehicle, fn ($q) => $q->orWhere('id', $vehicle->customer_id)))->orderBy('name')->get(),
            'brands' => VehicleBrand::where(fn ($q) => $q->where('active', true)->when($vehicle, fn ($q) => $q->orWhere('id', $vehicle->vehicle_brand_id)))->orderBy('name')->get()];
    }
}
