<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\SparePart;
use App\Support\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(Request $request, array $data): Order
    {
        return DB::transaction(function () use ($request, $data) {
            unset($data['status_notes']);
            $order = Order::create([...$data, 'number' => 'TMP-'.Str::random(16), 'created_by' => $request->user()->id]);
            $order->update(['number' => sprintf('OT-%s-%06d', $order->received_at->format('Y'), $order->id)]);
            $this->history($request, $order, 'Orden recibida y registrada en el sistema.');
            SecurityAudit::record($request, 'INSERT', 'orders', $order->id, 'Orden creada: '.$order->number.'.');

            return $order;
        });
    }

    public function update(Request $request, Order $order, array $data): Order
    {
        return DB::transaction(function () use ($request, $order, $data) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->assertEditable($locked);
            $oldStatus = $locked->order_status_id;
            $notes = $data['status_notes'] ?? null;
            unset($data['status_notes']);
            $status = OrderStatus::findOrFail($data['order_status_id']);
            $data['delivered_at'] = $status->is_final ? now() : null;
            $locked->update($data);
            if ($oldStatus !== $locked->order_status_id) {
                $this->history($request, $locked, $notes);
            }
            SecurityAudit::record($request, 'UPDATE', 'orders', $locked->id, 'Orden actualizada: '.$locked->number.'.');

            return $locked;
        });
    }

    public function saveItem(Request $request, Order $order, array $data, ?OrderItem $item = null): OrderItem
    {
        return DB::transaction(function () use ($request, $order, $data, $item) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->assertEditable($lockedOrder);
            $part = SparePart::whereKey($data['spare_part_id'])->lockForUpdate()->firstOrFail();
            if (! $part->active) {
                throw ValidationException::withMessages(['spare_part_id' => 'El repuesto seleccionado está inactivo.']);
            }
            if ($item) {
                $item = OrderItem::whereKey($item->id)->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();
                if ($item->spare_part_id !== $part->id) {
                    throw ValidationException::withMessages(['spare_part_id' => 'Para cambiar de repuesto, elimine esta línea y agregue una nueva.']);
                }
            } elseif (OrderItem::where('order_id', $lockedOrder->id)->where('spare_part_id', $part->id)->exists()) {
                throw ValidationException::withMessages(['spare_part_id' => 'El repuesto ya está agregado. Edite su cantidad en la orden.']);
            }
            $oldQuantity = $item?->quantity ?? 0;
            $difference = (int) $data['quantity'] - $oldQuantity;
            if ($difference > 0) {
                $this->moveStock($request, $lockedOrder, $part, 'OUT', $difference, 'Asignado a la orden '.$lockedOrder->number.'.');
            } elseif ($difference < 0) {
                $this->moveStock($request, $lockedOrder, $part, 'IN', abs($difference), 'Devuelto por ajuste de la orden '.$lockedOrder->number.'.');
            }
            $unitPrice = $item?->unit_price ?? $part->price;
            $values = ['quantity' => (int) $data['quantity'], 'line_total' => round((float) $unitPrice * (int) $data['quantity'], 2)];
            if ($item) {
                $item->update($values);
            } else {
                $item = OrderItem::create([...$values, 'order_id' => $lockedOrder->id, 'spare_part_id' => $part->id, 'unit_price' => $part->price]);
            }
            SecurityAudit::record($request, $oldQuantity ? 'UPDATE' : 'INSERT', 'order_items', $item->id, 'Repuesto actualizado en '.$lockedOrder->number.'.');

            return $item;
        });
    }

    public function removeItem(Request $request, Order $order, OrderItem $item): void
    {
        DB::transaction(function () use ($request, $order, $item) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->assertEditable($lockedOrder);
            $item = OrderItem::whereKey($item->id)->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();
            $part = SparePart::whereKey($item->spare_part_id)->lockForUpdate()->firstOrFail();
            $this->moveStock($request, $lockedOrder, $part, 'IN', $item->quantity, 'Devuelto al retirar el repuesto de la orden '.$lockedOrder->number.'.');
            $itemId = $item->id;
            $item->delete();
            SecurityAudit::record($request, 'DELETE', 'order_items', $itemId, 'Repuesto retirado de '.$lockedOrder->number.'.');
        });
    }

    public function cancel(Request $request, Order $order): void
    {
        DB::transaction(function () use ($request, $order) {
            $locked = Order::with('items')->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->assertEditable($locked);
            $cancelled = OrderStatus::where('name', 'Cancelada')->where('active', true)->firstOrFail();
            foreach ($locked->items as $item) {
                $part = SparePart::whereKey($item->spare_part_id)->lockForUpdate()->firstOrFail();
                $this->moveStock($request, $locked, $part, 'IN', $item->quantity, 'Devuelto por cancelación de la orden '.$locked->number.'.');
            }
            $locked->update(['order_status_id' => $cancelled->id, 'delivered_at' => now()]);
            $this->history($request, $locked, 'Orden cancelada; los repuestos fueron devueltos al inventario.');
            SecurityAudit::record($request, 'CANCEL', 'orders', $locked->id, 'Orden cancelada: '.$locked->number.'.');
        });
    }

    private function moveStock(Request $request, Order $order, SparePart $part, string $type, int $quantity, string $notes): void
    {
        $old = (int) $part->stock_quantity;
        $new = $type === 'OUT' ? $old - $quantity : $old + $quantity;
        if ($new < 0) {
            throw ValidationException::withMessages(['quantity' => "Stock insuficiente de {$part->name}. Hay $old unidades disponibles."]);
        }
        $part->update(['stock_quantity' => $new]);
        InventoryMovement::create(['spare_part_id' => $part->id, 'request_token' => (string) Str::uuid(), 'type' => $type, 'quantity' => $quantity, 'previous_balance' => $old, 'new_balance' => $new, 'order_id' => $order->id, 'user_id' => $request->user()->id, 'date' => now(), 'notes' => $notes]);
    }

    private function history(Request $request, Order $order, ?string $notes): void
    {
        OrderHistory::create(['order_id' => $order->id, 'order_status_id' => $order->order_status_id, 'user_id' => $request->user()->id, 'date' => now(), 'notes' => $notes]);
    }

    private function assertEditable(Order $order): void
    {
        if ($order->status?->is_final || $order->invoices()->exists()) {
            throw ValidationException::withMessages(['order' => 'La orden está finalizada o facturada y ya no puede modificarse.']);
        }
    }
}
