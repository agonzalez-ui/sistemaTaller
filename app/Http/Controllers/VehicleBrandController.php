<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveVehicleBrandRequest;
use App\Models\VehicleBrand;
use App\Support\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehicleBrandController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:80'], 'status' => ['nullable', 'in:active,inactive']]);
        $brands = VehicleBrand::withCount('vehicles')->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%$search%"))->when($filters['status'] ?? null, fn ($q, $status) => $q->where('active', $status === 'active'))->orderBy('name')->paginate(12)->withQueryString();

        return view('vehicle-brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('vehicle-brands.create');
    }

    public function store(SaveVehicleBrandRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $brand = VehicleBrand::create($request->validated());
            SecurityAudit::record($request, 'INSERT', 'vehicle_brands', $brand->id, 'Marca registrada: '.$brand->name.'.');
        });

        return redirect()->route('vehicle-brands.index')->with('success', 'Marca registrada correctamente.');
    }

    public function edit(VehicleBrand $vehicleBrand): View
    {
        return view('vehicle-brands.edit', ['brand' => $vehicleBrand]);
    }

    public function update(SaveVehicleBrandRequest $request, VehicleBrand $vehicleBrand): RedirectResponse
    {
        DB::transaction(function () use ($request, $vehicleBrand) {
            $vehicleBrand->update($request->validated());
            SecurityAudit::record($request, 'UPDATE', 'vehicle_brands', $vehicleBrand->id, 'Nombre o estado de la marca actualizado: '.$vehicleBrand->name.'.');
        });

        return redirect()->route('vehicle-brands.index')->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Request $request, VehicleBrand $vehicleBrand): RedirectResponse
    {
        DB::transaction(function () use ($request, $vehicleBrand) {
            $vehicleBrand->update(['active' => false]);
            SecurityAudit::record($request, 'DISABLE', 'vehicle_brands', $vehicleBrand->id, 'Marca desactivada; se conservan las motos registradas.');
        });

        return redirect()->route('vehicle-brands.index')->with('success', 'Marca desactivada. Las motos registradas conservan sus datos.');
    }
}
