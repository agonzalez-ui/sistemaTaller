<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSparePartBrandRequest;
use App\Models\SparePartBrand;
use App\Support\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SparePartBrandController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:80'], 'status' => ['nullable', 'in:active,inactive']]);
        $brands = SparePartBrand::withCount('spareParts')->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%$search%"))->when($filters['status'] ?? null, fn ($q, $status) => $q->where('active', $status === 'active'))->orderBy('name')->paginate(12)->withQueryString();

        return view('spare-part-brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('spare-part-brands.create');
    }

    public function store(SaveSparePartBrandRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $brand = SparePartBrand::create($request->validated());
            SecurityAudit::record($request, 'INSERT', 'spare_part_brands', $brand->id, 'Marca registrada: '.$brand->name.'.');
        });

        return redirect()->route('spare-part-brands.index')->with('success', 'Marca registrada correctamente.');
    }

    public function edit(SparePartBrand $sparePartBrand): View
    {
        return view('spare-part-brands.edit', ['brand' => $sparePartBrand]);
    }

    public function update(SaveSparePartBrandRequest $request, SparePartBrand $sparePartBrand): RedirectResponse
    {
        DB::transaction(function () use ($request, $sparePartBrand) {
            $sparePartBrand->update($request->validated());
            SecurityAudit::record($request, 'UPDATE', 'spare_part_brands', $sparePartBrand->id, 'Nombre o estado de la marca actualizado: '.$sparePartBrand->name.'.');
        });

        return redirect()->route('spare-part-brands.index')->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Request $request, SparePartBrand $sparePartBrand): RedirectResponse
    {
        DB::transaction(function () use ($request, $sparePartBrand) {
            $sparePartBrand->update(['active' => false]);
            SecurityAudit::record($request, 'DISABLE', 'spare_part_brands', $sparePartBrand->id, 'Marca desactivada; se conservan las repuestos registradas.');
        });

        return redirect()->route('spare-part-brands.index')->with('success', 'Marca desactivada. Las repuestos registradas conservan sus datos.');
    }
}
