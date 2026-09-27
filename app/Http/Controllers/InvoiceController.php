<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelInvoiceRequest;
use App\Http\Requests\CreateInvoiceRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:ISSUED,CANCELLED'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
        ]);
        $invoices = Invoice::with(['customer', 'vehicle.brand', 'order'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('number', 'like', "%$search%")
                ->orWhereHas('order', fn (Builder $query) => $query->where('number', 'like', "%$search%"))
                ->orWhereHas('customer', fn (Builder $query) => $query->where('name', 'like', "%$search%"))
                ->orWhereHas('vehicle', fn (Builder $query) => $query->where('license_plate', 'like', "%$search%"))))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->where('date', '>=', "$date 00:00:00"))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->where('date', '<=', "$date 23:59:59"))
            ->orderByDesc('date')->orderByDesc('id')->paginate(12)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request): View
    {
        $orders = Order::with(['customer', 'vehicle.brand', 'status', 'items'])
            ->whereHas('status', fn (Builder $query) => $query->where('allows_invoicing', true)->where('name', '!=', 'Cancelada'))
            ->whereDoesntHave('invoices')->orderByDesc('received_at')->get();
        $selectedOrder = $orders->firstWhere('id', $request->integer('order'));

        return view('invoices.create', compact('orders', 'selectedOrder'));
    }

    public function store(CreateInvoiceRequest $request, InvoiceService $service): RedirectResponse
    {
        $invoice = $service->create($request, $request->validated());

        return redirect()->route('invoices.show', $invoice)->with('success', 'Factura generada correctamente.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer.phones', 'vehicle.brand', 'order', 'creator', 'items']);

        return view('invoices.show', compact('invoice'));
    }

    public function destroy(CancelInvoiceRequest $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        $service->cancel($request, $invoice, $request->validated('cancellation_reason'));

        return redirect()->route('invoices.show', $invoice)->with('success', 'Factura anulada correctamente.');
    }
}
