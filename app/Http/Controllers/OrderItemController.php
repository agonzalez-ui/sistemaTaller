<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveOrderItemRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function store(SaveOrderItemRequest $request, Order $order, OrderService $service): RedirectResponse
    {
        $service->saveItem($request, $order, $request->validated());

        return redirect()->route('orders.show', $order)->with('success', 'Repuesto agregado y existencia descontada.');
    }

    public function update(SaveOrderItemRequest $request, Order $order, OrderItem $orderItem, OrderService $service): RedirectResponse
    {
        $service->saveItem($request, $order, $request->validated(), $orderItem);

        return redirect()->route('orders.show', $order)->with('success', 'Cantidad actualizada correctamente.');
    }

    public function destroy(Request $request, Order $order, OrderItem $orderItem, OrderService $service): RedirectResponse
    {
        $service->removeItem($request, $order, $orderItem);

        return redirect()->route('orders.show', $order)->with('success', 'Repuesto retirado y existencia devuelta.');
    }
}
