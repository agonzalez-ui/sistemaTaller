<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\SparePart;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleType;
use Database\Seeders\SimrhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SimrhSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['role_id' => 1])->save();
    $this->mechanic = User::factory()->create(['name' => 'Mecánico de prueba']);
    $this->mechanic->forceFill(['role_id' => 2])->save();
    $this->customer = Customer::create(['name' => 'Cliente Factura', 'identification_number' => '116380560', 'email' => 'cliente@example.com', 'created_by' => $this->admin->id]);
    $this->vehicle = Vehicle::create(['license_plate' => 'M123456', 'customer_id' => $this->customer->id, 'vehicle_brand_id' => VehicleBrand::where('name', 'Honda')->value('id'), 'vehicle_type_id' => VehicleType::where('name', 'Motocicleta')->value('id'), 'model' => 'CB190', 'year' => 2024, 'active' => true, 'created_by' => $this->admin->id]);
    $this->part = SparePart::create(['code' => 'REP-001', 'name' => 'Filtro', 'price' => 2500, 'stock_quantity' => 8, 'minimum_quantity' => 2, 'active' => true, 'created_by' => $this->admin->id]);
    $this->order = Order::create(['number' => 'OT-2026-000001', 'customer_id' => $this->customer->id, 'vehicle_id' => $this->vehicle->id, 'order_status_id' => OrderStatus::where('name', 'Listo')->value('id'), 'mechanic_id' => $this->mechanic->id, 'description' => 'Mantenimiento', 'diagnosis' => 'Mantenimiento realizado y funcionamiento verificado.', 'labor_cost' => 15000, 'received_at' => now(), 'created_by' => $this->admin->id]);
    OrderItem::create(['order_id' => $this->order->id, 'spare_part_id' => $this->part->id, 'quantity' => 2, 'unit_price' => 2500, 'line_total' => 5000]);
    $this->actingAs($this->admin);
});

test('creates one invoice from an eligible order with protected snapshots and server totals', function () {
    $this->get(route('invoices.create', ['order' => $this->order]))->assertSuccessful()->assertSee($this->order->number)->assertSee('Cliente Factura');
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 1000, 'tax_rate' => 13, 'total' => 1, 'number' => 'FORGED'])->assertSessionHasNoErrors();
    $invoice = Invoice::with('items')->firstOrFail();
    expect($invoice->number)->toBe('FAC-'.now()->format('Y').'-000001')
        ->and($invoice->customer_id)->toBe($this->customer->id)
        ->and($invoice->subtotal)->toBe('20000.00')->and($invoice->discount)->toBe('1000.00')
        ->and($invoice->tax_amount)->toBe('2470.00')->and($invoice->total)->toBe('21470.00')
        ->and($invoice->items)->toHaveCount(2)->and($invoice->items->first()->description)->toContain('Filtro');
    $this->part->update(['price' => 9000]);
    expect($invoice->items->first()->fresh()->unit_price)->toBe('2500.00');
    $this->get(route('invoices.show', $invoice))->assertSuccessful()->assertSee('₡21.470,00')->assertSee('Imprimir')->assertSee('Logo SIMRH')->assertSee('Registrar entrega');
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 13])->assertSessionHasErrors('order_id');
    expect(Invoice::count())->toBe(1);
});

test('records customer delivery after invoicing without unlocking billed details', function () {
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 13])->assertSessionHasNoErrors();
    $invoice = Invoice::firstOrFail();

    $this->post(route('orders.deliver', $this->order))->assertSessionHasNoErrors();

    expect($this->order->fresh()->status->name)->toBe('Entregado')
        ->and($this->order->fresh()->delivered_at)->not->toBeNull()
        ->and($invoice->fresh()->status)->toBe('ISSUED')
        ->and(Invoice::count())->toBe(1);

    $this->post(route('orders.deliver', $this->order))->assertSessionHasErrors('order');
    $this->get(route('orders.edit', $this->order))->assertStatus(409);
});

test('does not allow delivery without an issued invoice', function () {
    $this->post(route('orders.deliver', $this->order))->assertSessionHasErrors('order');

    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 0]);
    $invoice = Invoice::firstOrFail();
    $this->delete(route('invoices.destroy', $invoice), ['cancellation_reason' => 'Factura creada por error'])->assertSessionHasNoErrors();
    $this->post(route('orders.deliver', $this->order))->assertSessionHasErrors('order');

    expect($this->order->fresh()->status->name)->toBe('Listo');
});

test('rejects unfinished empty and invalid invoice amounts', function () {
    $this->order->update(['order_status_id' => OrderStatus::where('name', 'En reparación')->value('id')]);
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 13])->assertSessionHasErrors('order_id');
    $this->order->update(['order_status_id' => OrderStatus::where('name', 'Listo')->value('id')]);
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 20001, 'tax_rate' => 13])->assertSessionHasErrors('discount');
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 101])->assertSessionHasErrors('tax_rate');
    $this->order->items()->delete();
    $this->order->update(['labor_cost' => 0]);
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 0])->assertSessionHasErrors('order_id');
    expect(Invoice::count())->toBe(0);
});

test('rejects invoicing an otherwise eligible legacy order without mechanic or diagnosis', function () {
    $this->order->update(['mechanic_id' => null, 'diagnosis' => null]);

    $this->get(route('invoices.create'))->assertDontSee($this->order->number);
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 13])->assertSessionHasErrors('order_id');

    expect(Invoice::count())->toBe(0);
});

test('cancels without deleting the invoice order or changing inventory', function () {
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 0]);
    $invoice = Invoice::firstOrFail();
    $stock = $this->part->stock_quantity;
    $this->delete(route('invoices.destroy', $invoice), ['cancellation_reason' => 'Error de digitación'])->assertSessionHasNoErrors();
    expect($invoice->fresh()->status)->toBe('CANCELLED')->and($invoice->fresh()->cancelled_at)->not->toBeNull()
        ->and($invoice->fresh()->cancellation_reason)->toBe('Error de digitación')->and($this->part->fresh()->stock_quantity)->toBe($stock)
        ->and(Order::find($this->order->id))->not->toBeNull();
    $this->delete(route('invoices.destroy', $invoice), ['cancellation_reason' => 'Otro motivo'])->assertSessionHasErrors('cancellation_reason');
});

test('enforces billing roles and invoice filters', function () {
    $this->post(route('invoices.store'), ['order_id' => $this->order->id, 'discount' => 0, 'tax_rate' => 0]);
    $invoice = Invoice::firstOrFail();
    $seller = User::factory()->create();
    $seller->forceFill(['role_id' => 3])->save();
    $this->actingAs($seller);
    $this->get(route('invoices.index', ['search' => 'M123456']))->assertSuccessful()->assertSee($invoice->number);
    $this->get(route('invoices.index', ['from' => '2030-01-01']))->assertDontSee($invoice->number);
    $this->delete(route('invoices.destroy', $invoice), ['cancellation_reason' => 'No autorizado'])->assertForbidden();
    $mechanic = User::factory()->create();
    $mechanic->forceFill(['role_id' => 2])->save();
    $this->actingAs($mechanic);
    $this->get(route('invoices.index'))->assertForbidden();
    $auditor = User::factory()->create();
    $auditor->forceFill(['role_id' => 4])->save();
    $this->actingAs($auditor);
    $this->get(route('invoices.show', $invoice))->assertSuccessful()->assertDontSee('Anular factura');
    $this->get(route('invoices.create'))->assertForbidden();
});
