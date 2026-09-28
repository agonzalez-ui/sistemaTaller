<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Support\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function create(Request $request, array $data): Invoice
    {
        return DB::transaction(function () use ($request, $data) {
            $order = Order::with(['items.sparePart', 'status'])->whereKey($data['order_id'])->lockForUpdate()->firstOrFail();
            if (! $order->status?->allows_invoicing || $order->status?->name === 'Cancelada') {
                throw ValidationException::withMessages(['order_id' => 'La orden debe estar lista o entregada para poder facturarla.']);
            }
            if (! $order->mechanic_id || blank($order->diagnosis)) {
                throw ValidationException::withMessages(['order_id' => 'Complete el mecánico, diagnóstico y trabajo realizado antes de facturar la orden.']);
            }
            if (Invoice::where('order_id', $order->id)->exists()) {
                throw ValidationException::withMessages(['order_id' => 'Esta orden ya tiene una factura.']);
            }

            $labor = round((float) $order->labor_cost, 2);
            $subtotal = round((float) $order->items->sum(fn ($item) => (float) $item->line_total) + $labor, 2);
            if ($subtotal <= 0) {
                throw ValidationException::withMessages(['order_id' => 'La orden debe tener mano de obra o repuestos con valor para generar una factura.']);
            }
            $discount = round((float) $data['discount'], 2);
            if ($discount > $subtotal) {
                throw ValidationException::withMessages(['discount' => 'El descuento no puede superar el subtotal de la orden.']);
            }
            $taxRate = round((float) $data['tax_rate'], 2);
            $taxAmount = round(($subtotal - $discount) * ($taxRate / 100), 2);

            $invoice = Invoice::create([
                'number' => 'TMP-'.Str::random(16), 'order_id' => $order->id,
                'customer_id' => $order->customer_id, 'vehicle_id' => $order->vehicle_id,
                'date' => now(), 'subtotal' => $subtotal, 'discount' => $discount,
                'tax_rate' => $taxRate, 'tax_amount' => $taxAmount,
                'total' => round($subtotal - $discount + $taxAmount, 2),
                'status' => 'ISSUED', 'created_by' => $request->user()->id,
            ]);
            $invoice->update(['number' => sprintf('FAC-%s-%06d', $invoice->date->format('Y'), $invoice->id)]);

            foreach ($order->items as $item) {
                $invoice->items()->create([
                    'line_type' => 'PART', 'spare_part_id' => $item->spare_part_id,
                    'description' => trim(($item->sparePart?->code ? $item->sparePart->code.' · ' : '').($item->sparePart?->name ?? 'Repuesto')),
                    'quantity' => $item->quantity, 'unit_price' => $item->unit_price, 'line_total' => $item->line_total,
                ]);
            }
            if ($labor > 0) {
                $invoice->items()->create([
                    'line_type' => 'SERVICE', 'description' => 'Mano de obra de la orden '.$order->number,
                    'quantity' => 1, 'unit_price' => $labor, 'line_total' => $labor,
                ]);
            }
            SecurityAudit::record($request, 'INSERT', 'invoices', $invoice->id, 'Factura emitida: '.$invoice->number.' para '.$order->number.'.');

            return $invoice;
        });
    }

    public function cancel(Request $request, Invoice $invoice, string $reason): void
    {
        DB::transaction(function () use ($request, $invoice, $reason) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'CANCELLED') {
                throw ValidationException::withMessages(['cancellation_reason' => 'La factura ya se encuentra anulada.']);
            }
            $locked->update(['status' => 'CANCELLED', 'cancelled_at' => now(), 'cancellation_reason' => $reason]);
            SecurityAudit::record($request, 'CANCEL', 'invoices', $locked->id, 'Factura anulada: '.$locked->number.'. Motivo: '.$reason);
        });
    }
}
