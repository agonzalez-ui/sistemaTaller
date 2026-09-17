<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSparePartRequest;
use App\Models\SparePart;
use App\Models\SparePartBrand;
use App\Support\SecurityAudit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SparePartController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:active,inactive'], 'stock' => ['nullable', 'in:low,empty'], 'brand' => ['nullable', 'integer', 'exists:spare_part_brands,id']]);
        $parts = SparePart::with('brand')->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('code', 'like', "%$s%")->orWhere('name', 'like', "%$s%")))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('active', $s === 'active'))
            ->when($filters['brand'] ?? null, fn ($q, $id) => $q->where('spare_part_brand_id', $id))
            ->when($filters['stock'] ?? null, fn ($q, $s) => $s === 'empty' ? $q->where('stock_quantity', 0) : $q->whereColumn('stock_quantity', '<=', 'minimum_quantity'))
            ->orderBy('name')->paginate(12)->withQueryString();
        $summary = ['active' => SparePart::where('active', true)->count(), 'low' => SparePart::where('active', true)->whereColumn('stock_quantity', '<=', 'minimum_quantity')->count(), 'empty' => SparePart::where('active', true)->where('stock_quantity', 0)->count()];

        return view('spareparts.index', ['parts' => $parts, 'brands' => SparePartBrand::orderBy('name')->get(), 'summary' => $summary]);
    }

    public function create(): View
    {
        return view('spareparts.create', ['brands' => $this->brands()]);
    }

    public function store(SaveSparePartRequest $request): RedirectResponse
    {
        $part = DB::transaction(function () use ($request) {
            $part = SparePart::create([...$request->validated(), 'stock_quantity' => 0, 'created_by' => $request->user()->id]);
            SecurityAudit::record($request, 'INSERT', 'spare_parts', $part->id, 'Repuesto registrado: '.$part->code.'.');

            return $part;
        });

        return redirect()->route('spareparts.show', $part)->with('success', 'Repuesto registrado. Registre una entrada para agregar existencias.');
    }

    public function show(Request $request, SparePart $sparepart): View
    {
        $filters = $request->validate(['type' => ['nullable', 'in:IN,OUT,ADJUST'], 'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])]]);
        $movements = $sparepart->inventoryMovements()->with('user')->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))->when($filters['from'] ?? null, fn ($q, $date) => $q->where('date', '>=', $date.' 00:00:00'))->when($filters['to'] ?? null, fn ($q, $date) => $q->where('date', '<=', $date.' 23:59:59'))->orderByDesc('id')->paginate(15)->withQueryString();

        return view('spareparts.show', ['part' => $sparepart->load('brand'), 'movements' => $movements]);
    }

    public function edit(SparePart $sparepart): View
    {
        return view('spareparts.edit', ['part' => $sparepart, 'brands' => $this->brands($sparepart)]);
    }

    public function update(SaveSparePartRequest $request, SparePart $sparepart): RedirectResponse
    {
        DB::transaction(function () use ($request, $sparepart) {
            $sparepart->update($request->validated());
            SecurityAudit::record($request, 'UPDATE', 'spare_parts', $sparepart->id, 'Datos o estado del repuesto actualizados: '.$sparepart->code.'.');
        });

        return redirect()->route('spareparts.show', $sparepart)->with('success', 'Repuesto actualizado correctamente.');
    }

    public function destroy(Request $request, SparePart $sparepart): RedirectResponse
    {
        DB::transaction(function () use ($request, $sparepart) {
            $sparepart->update(['active' => false]);
            SecurityAudit::record($request, 'DISABLE', 'spare_parts', $sparepart->id, 'Repuesto desactivado; stock e historial conservados.');
        });

        return redirect()->route('spareparts.index')->with('success', 'Repuesto desactivado. Se conservan sus existencias e historial.');
    }

    private function brands(?SparePart $part = null): Collection
    {
        return SparePartBrand::where(fn ($q) => $q->where('active', true)->when($part, fn ($q) => $q->orWhere('id', $part->spare_part_brand_id)))->orderBy('name')->get();
    }
}
