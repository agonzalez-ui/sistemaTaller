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
use App\Services\ReportExcelExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function billing(Request $request): View
    {
        return $this->render($request, 'billing');
    }

    public function orders(Request $request): View
    {
        return $this->render($request, 'orders');
    }

    public function inventory(Request $request): View
    {
        return $this->render($request, 'inventory');
    }

    public function accessLogs(Request $request): View
    {
        return $this->render($request, 'access');
    }

    public function activityLogs(Request $request): View
    {
        return $this->render($request, 'activity');
    }

    public function excel(Request $request, string $type, ReportExcelExporter $exporter): BinaryFileResponse
    {
        abort_unless(in_array($type, ['billing', 'orders', 'inventory', 'access', 'activity'], true), 404);
        $data = $this->dataset($request, $type);
        $count = (clone $data['query'])->count();
        if ($count > 10000) {
            throw ValidationException::withMessages(['from' => 'La exportación supera 10 000 registros. Aplique un rango de fechas o filtros más específicos.']);
        }

        return $exporter->download($type, $data['title'], $data['description'], $data['query'], $data['totals'], $request->user());
    }

    private function render(Request $request, string $type): View
    {
        $data = $this->dataset($request, $type);
        $rows = $data['query']->paginate(50)->withQueryString();

        return view('reports.show', [...$data, 'type' => $type, 'rows' => $rows]);
    }

    private function dataset(Request $request, string $type): array
    {
        return match ($type) {
            'billing' => $this->billingData($request),
            'orders' => $this->ordersData($request),
            'inventory' => $this->inventoryData($request),
            'access' => $this->accessData($request),
            'activity' => $this->activityData($request),
        };
    }

    private function billingData(Request $request): array
    {
        $filters = $this->dateFilters($request, ['status' => ['nullable', 'in:ISSUED,CANCELLED'], 'customer' => ['nullable', 'integer', 'exists:customers,id']]);
        $query = Invoice::with(['customer', 'order'])->when($filters['status'] ?? null, fn (Builder $q, string $v) => $q->where('status', $v))->when($filters['customer'] ?? null, fn (Builder $q, int $v) => $q->where('customer_id', $v));
        $this->applyDateRange($query, 'date', $filters);
        $totals = [
            'Facturas' => (clone $query)->count(), 'Emitidas' => (clone $query)->where('status', 'ISSUED')->count(),
            'Anuladas' => (clone $query)->where('status', 'CANCELLED')->count(),
            'Total emitido' => '₡'.number_format((float) (clone $query)->where('status', 'ISSUED')->sum('total'), 2, ',', '.'),
        ];

        return $this->data('Facturación', 'Facturas emitidas y anuladas durante el periodo seleccionado.', $query->orderByDesc('date')->orderByDesc('id'), $totals, ['customers' => Customer::orderBy('name')->get()]);
    }

    private function ordersData(Request $request): array
    {
        $filters = $this->dateFilters($request, ['status' => ['nullable', 'integer', 'exists:order_statuses,id'], 'mechanic' => ['nullable', 'integer', 'exists:users,id']]);
        $query = Order::with(['customer', 'vehicle', 'status', 'mechanic'])->withSum('items', 'line_total')->when($filters['status'] ?? null, fn (Builder $q, int $v) => $q->where('order_status_id', $v))->when($filters['mechanic'] ?? null, fn (Builder $q, int $v) => $q->where('mechanic_id', $v));
        $this->applyDateRange($query, 'received_at', $filters);
        $parts = (float) (clone $query)->getQuery()->sum(DB::raw('(SELECT COALESCE(SUM(line_total),0) FROM order_items WHERE order_items.order_id = orders.id)'));
        $labor = (float) (clone $query)->sum('labor_cost');
        $totals = [
            'Órdenes' => (clone $query)->count(),
            'Finalizadas' => (clone $query)->whereHas('status', fn (Builder $q) => $q->where('is_final', true))->count(),
            'Repuestos' => '₡'.number_format($parts, 2, ',', '.'), 'Valor registrado' => '₡'.number_format($parts + $labor, 2, ',', '.'),
        ];

        return $this->data('Órdenes de trabajo', 'Seguimiento de trabajos por estado, mecánico y fecha de recepción.', $query->orderByDesc('received_at')->orderByDesc('id'), $totals, ['statuses' => OrderStatus::orderBy('sort_order')->get(), 'mechanics' => User::whereHas('role', fn (Builder $q) => $q->where('name', 'Mecánico'))->orderBy('name')->get()]);
    }

    private function inventoryData(Request $request): array
    {
        $filters = $request->validate(['brand' => ['nullable', 'integer', 'exists:spare_part_brands,id'], 'state' => ['nullable', 'in:active,inactive'], 'stock' => ['nullable', 'in:low,available,out']]);
        $query = SparePart::with('brand')->when($filters['brand'] ?? null, fn (Builder $q, int $v) => $q->where('spare_part_brand_id', $v))->when($filters['state'] ?? null, fn (Builder $q, string $v) => $q->where('active', $v === 'active'))->when(($filters['stock'] ?? null) === 'low', fn (Builder $q) => $q->whereColumn('stock_quantity', '<=', 'minimum_quantity'))->when(($filters['stock'] ?? null) === 'available', fn (Builder $q) => $q->where('stock_quantity', '>', 0))->when(($filters['stock'] ?? null) === 'out', fn (Builder $q) => $q->where('stock_quantity', 0));
        $totals = [
            'Repuestos' => (clone $query)->count(), 'Unidades' => (clone $query)->sum('stock_quantity'),
            'Stock bajo' => (clone $query)->whereColumn('stock_quantity', '<=', 'minimum_quantity')->count(),
            'Valor del inventario' => '₡'.number_format((float) (clone $query)->sum(DB::raw('stock_quantity * price')), 2, ',', '.'),
        ];

        return $this->data('Inventario de repuestos', 'Existencias, mínimos y valor de venta del inventario.', $query->orderBy('name')->orderBy('id'), $totals, ['brands' => SparePartBrand::orderBy('name')->get()]);
    }

    private function accessData(Request $request): array
    {
        $filters = $this->dateFilters($request, ['user' => ['nullable', 'integer', 'exists:users,id']]);
        $query = AccessLog::with('user')->when($filters['user'] ?? null, fn (Builder $q, int $v) => $q->where('user_id', $v));
        $this->applyDateRange($query, 'logged_in_at', $filters);
        $totals = ['Ingresos' => (clone $query)->count(), 'Sesiones cerradas' => (clone $query)->whereNotNull('logged_out_at')->count(), 'Sesiones abiertas' => (clone $query)->whereNull('logged_out_at')->count()];

        return $this->data('Bitácora de ingresos y salidas', 'Sesiones iniciadas y finalizadas por los usuarios del sistema.', $query->orderByDesc('logged_in_at')->orderByDesc('id'), $totals, ['users' => User::orderBy('name')->get()]);
    }

    private function activityData(Request $request): array
    {
        $filters = $this->dateFilters($request, ['user' => ['nullable', 'integer', 'exists:users,id'], 'action' => ['nullable', 'string', 'max:20']]);
        $query = ActivityLog::with('user')->when($filters['user'] ?? null, fn (Builder $q, int $v) => $q->where('user_id', $v))->when($filters['action'] ?? null, fn (Builder $q, string $v) => $q->where('action', $v));
        $this->applyDateRange($query, 'occurred_at', $filters);
        $totals = ['Movimientos' => (clone $query)->count(), 'Usuarios' => (clone $query)->distinct('user_id')->count('user_id'), 'Tipos de movimiento' => (clone $query)->distinct('action')->count('action')];

        return $this->data('Bitácora de movimientos', 'Acciones realizadas por los usuarios sobre la información del sistema.', $query->orderByDesc('occurred_at')->orderByDesc('id'), $totals, ['users' => User::orderBy('name')->get(), 'actions' => ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action')]);
    }

    private function dateFilters(Request $request, array $extra): array
    {
        return $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])], ...$extra]);
    }

    private function applyDateRange(Builder $query, string $column, array $filters): void
    {
        $query->when($filters['from'] ?? null, fn (Builder $q, string $v) => $q->where($column, '>=', "$v 00:00:00"))->when($filters['to'] ?? null, fn (Builder $q, string $v) => $q->where($column, '<=', "$v 23:59:59"));
    }

    private function data(string $title, string $description, Builder $query, array $totals, array $catalogs): array
    {
        return compact('title', 'description', 'query', 'totals', 'catalogs');
    }
}
