<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\SparePart;
use App\Support\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function record(Request $request, SparePart $part, array $data): InventoryMovement
    {
        return DB::transaction(function () use ($request, $part, $data) {
            $locked = SparePart::whereKey($part->id)->lockForUpdate()->firstOrFail();
            $previous = InventoryMovement::where('request_token', $data['request_token'])->first();
            if ($previous) {
                abort_unless($previous->spare_part_id === $locked->id && $previous->user_id === $request->user()->id, 409);

                return $previous;
            }
            if (! $locked->active) {
                throw ValidationException::withMessages(['quantity' => 'Reactive el repuesto antes de registrar un movimiento.']);
            }
            $old = (int) $locked->stock_quantity;
            $data['quantity'] = (int) $data['quantity'];
            $new = match ($data['type']) {
                'IN' => $old + $data['quantity'],'OUT' => $old - $data['quantity'],'ADJUST' => $data['quantity']
            };
            if ($new < 0) {
                throw ValidationException::withMessages(['quantity' => "Stock insuficiente. Hay $old unidades disponibles."]);
            }
            if ($new > 2147483647) {
                throw ValidationException::withMessages(['quantity' => 'El saldo supera el máximo permitido.']);
            }
            if ($new === $old) {
                throw ValidationException::withMessages(['quantity' => 'El conteo coincide con el saldo actual; no es necesario un ajuste.']);
            }
            $locked->update(['stock_quantity' => $new]);
            $movement = InventoryMovement::create(['spare_part_id' => $locked->id, 'type' => $data['type'], 'quantity' => abs($new - $old), 'previous_balance' => $old, 'new_balance' => $new, 'user_id' => $request->user()->id, 'date' => now(), 'notes' => $data['notes'], 'request_token' => $data['request_token']]);
            SecurityAudit::record($request, 'UPDATE', 'spare_parts', $locked->id, "Movimiento {$movement->id}: {$data['type']}; saldo $old → $new.");

            return $movement;
        });
    }
}
