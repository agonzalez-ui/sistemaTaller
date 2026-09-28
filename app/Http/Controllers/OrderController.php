<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\SparePart;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'integer', 'exists:order_statuses,id'],
            'mechanic' => ['nullable', 'integer', 'exists:users,id'], 'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
        ]);
        $orders = Order::with(['customer', 'vehicle.brand', 'status', 'mechanic'])->withSum('items', 'line_total')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('number', 'like', "%$search%")->orWhereHas('customer', fn (Builder $query) => $query->where('name', 'like', "%$search%"))
                ->orWhereHas('vehicle', fn (Builder $query) => $query->where('license_plate', 'like', "%$search%"))))
            ->when($filters['status'] ?? null, fn (Builder $query, int $status) => $query->where('order_status_id', $status))
            ->when($filters['mechanic'] ?? null, fn (Builder $query, int $mechanic) => $query->where('mechanic_id', $mechanic))
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->where('received_at', '>=', "$date 00:00:00"))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->where('received_at', '<=', "$date 23:59:59"))
            ->orderByDesc('received_at')->orderByDesc('id')->paginate(12)->withQueryString();

        return view('orders.index', ['orders' => $orders, ...$this->catalogs()]);
    }

    public function create(): View
    {
        return view('orders.create', $this->formData());
    }

    public function store(SaveOrderRequest $request, OrderService $service): RedirectResponse
    {
        $order = $service->create($request, $request->validated());

        return redirect()->route('orders.show', $order)->with('success', 'Orden de trabajo registrada correctamente.');
    }

    public function show(Order $order): View
    {
        $order->load(['customer.phones', 'vehicle.brand', 'status', 'mechanic', 'creator', 'items.sparePart.brand', 'histories.status', 'histories.user', 'invoices']);
        $partsTotal = (float) $order->items->sum('line_total');

        return view('orders.show', ['order' => $order, 'parts' => SparePart::with('brand')->where('active', true)->where('stock_quantity', '>', 0)->orderBy('name')->get(), 'partsTotal' => $partsTotal, 'total' => $partsTotal + (float) $order->labor_cost]);
    }

    public function edit(Order $order): View
    {
        abort_if($order->status?->is_final || $order->invoices()->exists(), 409, 'La orden ya no puede modificarse.');

        return view('orders.edit', ['order' => $order, ...$this->formData($order)]);
    }

    public function update(SaveOrderRequest $request, Order $order, OrderService $service): RedirectResponse
    {
        $service->update($request, $order, $request->validated());

        return redirect()->route('orders.show', $order)->with('success', 'Orden actualizada correctamente.');
    }

    public function destroy(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        $service->cancel($request, $order);

        return redirect()->route('orders.show', $order)->with('success', 'Orden cancelada y repuestos devueltos al inventario.');
    }

    public function deliver(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        $service->deliver($request, $order);

        return redirect()->route('orders.show', $order)->with('success', 'Entrega registrada correctamente. La orden quedó finalizada.');
    }

    private function catalogs(): array
    {
        return ['statuses' => OrderStatus::where('active', true)->orderBy('sort_order')->get(), 'mechanics' => User::where('active', true)->whereHas('role', fn (Builder $query) => $query->where('name', 'Mecánico')->where('active', true))->orderBy('name')->get()];
    }

    private function formData(?Order $order = null): array
    {
        $customers = Customer::where(fn (Builder $query) => $query->where('active', true)->when($order, fn (Builder $query) => $query->orWhereKey($order->customer_id)))->orderBy('name')->get();
        $vehicles = Vehicle::with(['brand', 'customer', 'type'])->whereHas('type', fn (Builder $query) => $query->where('name', 'Motocicleta'))
            ->where(fn (Builder $query) => $query->where('active', true)->when($order, fn (Builder $query) => $query->orWhereKey($order->vehicle_id)))->orderBy('license_plate')->get();

        $catalogs = $this->catalogs();
        $catalogs['statuses'] = $catalogs['statuses']->where('name', '!=', 'Cancelada')->values();
        if (! $order) {
            $catalogs['statuses'] = $catalogs['statuses']->where('name', 'Recibido')->values();
        }

        return [...$catalogs, 'customers' => $customers, 'vehicles' => $vehicles];
    }
}
