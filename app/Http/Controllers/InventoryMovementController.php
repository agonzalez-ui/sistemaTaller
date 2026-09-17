<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryMovementRequest;
use App\Models\SparePart;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;

class InventoryMovementController extends Controller
{
    public function store(StoreInventoryMovementRequest $request, SparePart $sparepart, InventoryService $inventory): RedirectResponse
    {
        $inventory->record($request, $sparepart, $request->validated());

        return redirect()->route('spareparts.show', $sparepart)->with('success', 'Movimiento registrado correctamente.');
    }
}
