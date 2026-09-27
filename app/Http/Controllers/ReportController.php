<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\SparePart;
use App\Models\SparePartBrand;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function billing(Request $request): View
    {
        $filters = $this->dateFilters($request, [
            'status' => ['nullable', 'in:ISSUED,CANCELLED'],
            'customer' => ['nullable', 'integer', 'exists:customers,id'],
        ]);
        $query = Invoice::with(['customer', 'order'])->when($filters['status'] ?? null, fn (Builder $q, string $v) => $q->where('status', $v))
            ->when($filters['customer'] ?? null, fn (Builder $q, int $v) => $q->where('customer_id', $v));
        $this->applyDateRange($query, 'date', $filters);
        $rows = $query->orderBy('date')->orderBy('id')->get();

        return $this->report('Facturación', 'Facturas emitidas y anuladas durante el periodo seleccionado.', 'billing', $rows, [
            'Facturas' => $rows->count(),
            'Emitidas' => $rows->where('status', 'ISSUED')->count(),
            'Anuladas' => $rows->where('status', 'CANCELLED')->count(),
            'Total emitido' => '₡'.number_format((float) $rows->where('status', 'ISSUED')->sum('total'), 2, ',', '.'),
        ], ['customers' => Customer::orderBy('name')->get()]);
    }

    public function orders(Request $request): View
    {
        $filters = $this->dateFilters($request, [
            'status' => ['nullable', 'integer', 'exists:order_statuses,id'],
            'mechanic' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $query = Order::with(['customer', 'vehicle', 'status', 'mechanic'])->withSum('items', 'line_total')
            ->when($filters['status'] ?? null, fn (Builder $q, int $v) => $q->where('order_status_id', $v))
            ->when($filters['mechanic'] ?? null, fn (Builder $q, int $v) => $q->where('mechanic_id', $v));
        $this->applyDateRange($query, 'received_at', $filters);
        $rows = $query->orderBy('received_at')->orderBy('id')->get();
        $projected = $rows->sum(fn (Order $order) => (float) $order->labor_cost + (float) $order->items_sum_line_total);

        return $this->report('Órdenes de trabajo', 'Seguimiento de trabajos por estado, mecánico y fecha de recepción.', 'orders', $rows, [
            'Órdenes' => $rows->count(), 'Finalizadas' => $rows->where('status.is_final', true)->count(),
            'Repuestos' => '₡'.number_format((float) $rows->sum('items_sum_line_total'), 2, ',', '.'),
            'Valor registrado' => '₡'.number_format((float) $projected, 2, ',', '.'),
        ], ['statuses' => OrderStatus::orderBy('sort_order')->get(), 'mechanics' => User::whereHas('role', fn (Builder $q) => $q->where('name', 'Mecánico'))->orderBy('name')->get()]);
    }

    public function inventory(Request $request): View
    {
        $filters = $request->validate([
            'brand' => ['nullable', 'integer', 'exists:spare_part_brands,id'],
            'state' => ['nullable', 'in:active,inactive'],
            'stock' => ['nullable', 'in:low,available,out'],
        ]);
        $rows = SparePart::with('brand')
            ->when($filters['brand'] ?? null, fn (Builder $q, int $v) => $q->where('spare_part_brand_id', $v))
            ->when($filters['state'] ?? null, fn (Builder $q, string $v) => $q->where('active', $v === 'active'))
            ->when(($filters['stock'] ?? null) === 'low', fn (Builder $q) => $q->whereColumn('stock_quantity', '<=', 'minimum_quantity'))
            ->when(($filters['stock'] ?? null) === 'available', fn (Builder $q) => $q->where('stock_quantity', '>', 0))
            ->when(($filters['stock'] ?? null) === 'out', fn (Builder $q) => $q->where('stock_quantity', 0))
            ->orderBy('name')->get();

        return $this->report('Inventario de repuestos', 'Existencias, mínimos y valor de venta del inventario.', 'inventory', $rows, [
            'Repuestos' => $rows->count(), 'Unidades' => $rows->sum('stock_quantity'),
            'Stock bajo' => $rows->filter(fn (SparePart $part) => $part->stock_quantity <= $part->minimum_quantity)->count(),
            'Valor del inventario' => '₡'.number_format($rows->sum(fn (SparePart $part) => $part->stock_quantity * (float) $part->price), 2, ',', '.'),
        ], ['brands' => SparePartBrand::orderBy('name')->get()]);
    }

    public function accessLogs(Request $request): View
    {
        $filters = $this->dateFilters($request, ['user' => ['nullable', 'integer', 'exists:users,id']]);
        $query = AccessLog::with('user')->when($filters['user'] ?? null, fn (Builder $q, int $v) => $q->where('user_id', $v));
        $this->applyDateRange($query, 'logged_in_at', $filters);
        $rows = $query->orderBy('logged_in_at')->orderBy('id')->get();

        return $this->report('Bitácora de ingresos y salidas', 'Sesiones iniciadas y finalizadas por los usuarios del sistema.', 'access', $rows, [
            'Ingresos' => $rows->count(), 'Sesiones cerradas' => $rows->whereNotNull('logged_out_at')->count(),
            'Sesiones abiertas' => $rows->whereNull('logged_out_at')->count(),
        ], ['users' => User::orderBy('name')->get()]);
    }

    public function activityLogs(Request $request): View
    {
        $filters = $this->dateFilters($request, [
            'user' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:20'],
        ]);
        $query = ActivityLog::with('user')->when($filters['user'] ?? null, fn (Builder $q, int $v) => $q->where('user_id', $v))
            ->when($filters['action'] ?? null, fn (Builder $q, string $v) => $q->where('action', $v));
        $this->applyDateRange($query, 'occurred_at', $filters);
        $rows = $query->orderBy('occurred_at')->orderBy('id')->get();

        return $this->report('Bitácora de movimientos', 'Acciones realizadas por los usuarios sobre la información del sistema.', 'activity', $rows, [
            'Movimientos' => $rows->count(), 'Usuarios' => $rows->pluck('user_id')->unique()->count(),
            'Tipos de movimiento' => $rows->pluck('action')->unique()->count(),
        ], ['users' => User::orderBy('name')->get(), 'actions' => ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action')]);
    }

    private function dateFilters(Request $request, array $extra): array
    {
        return $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
            ...$extra,
        ]);
    }

    private function applyDateRange(Builder $query, string $column, array $filters): void
    {
        $query->when($filters['from'] ?? null, fn (Builder $q, string $v) => $q->where($column, '>=', "$v 00:00:00"))
            ->when($filters['to'] ?? null, fn (Builder $q, string $v) => $q->where($column, '<=', "$v 23:59:59"));
    }

    private function report(string $title, string $description, string $type, $rows, array $totals, array $catalogs = []): View
    {
        return view('reports.show', compact('title', 'description', 'type', 'rows', 'totals', 'catalogs'));
    }
}
