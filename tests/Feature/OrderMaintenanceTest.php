<?php

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderHistory;
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
    $this->mechanic = User::factory()->create(['name' => 'Julian Rodriguez']);
    $this->mechanic->forceFill(['role_id' => 2])->save();
    $this->customer = Customer::create(['name' => 'Cliente Principal', 'identification_number' => '116380560', 'created_by' => $this->admin->id]);
    $this->otherCustomer = Customer::create(['name' => 'Otro Cliente', 'identification_number' => '116380561', 'created_by' => $this->admin->id]);
    $this->vehicle = Vehicle::create(['license_plate' => 'M123456', 'customer_id' => $this->customer->id, 'vehicle_brand_id' => VehicleBrand::where('name', 'Honda')->value('id'), 'vehicle_type_id' => VehicleType::where('name', 'Motocicleta')->value('id'), 'model' => 'CB190', 'year' => 2024, 'active' => true, 'created_by' => $this->admin->id]);
    $this->part = SparePart::create(['code' => 'REP-001', 'name' => 'Filtro', 'price' => 2500, 'stock_quantity' => 10, 'minimum_quantity' => 2, 'active' => true, 'created_by' => $this->admin->id]);
    $this->received = OrderStatus::where('name', 'Recibido')->firstOrFail();
    $this->orderData = ['customer_id' => $this->customer->id, 'vehicle_id' => $this->vehicle->id, 'order_status_id' => $this->received->id, 'mechanic_id' => $this->mechanic->id, 'description' => 'Ruido en el motor', 'diagnosis' => null, 'labor_cost' => '15000.00', 'received_at' => '2026-09-27T10:30'];
    $this->actingAs($this->admin);
});

function createTestOrder($test): Order
{
    $test->post(route('orders.store'), $test->orderData)->assertSessionHasNoErrors();

    return Order::firstOrFail();
}

test('creates a numbered order linked to its owner motorcycle and mechanic', function () {
    $this->get(route('orders.create'))->assertSuccessful()->assertSee('Cliente Principal')->assertSee('M123456')->assertSee('Julian Rodriguez');
    $this->post(route('orders.store'), [...$this->orderData, 'number' => 'FORGED', 'created_by' => 999])->assertSessionHasNoErrors();
    $order = Order::firstOrFail();
    expect($order->number)->toBe('OT-2026-000001')->and($order->created_by)->toBe($this->admin->id)->and($order->customer_id)->toBe($this->customer->id)->and($order->vehicle_id)->toBe($this->vehicle->id);
    expect(OrderHistory::count())->toBe(1)->and($order->histories()->first()->notes)->toContain('recibida');
    $this->get(route('orders.show', $order))->assertSuccessful()->assertSee($order->number)->assertSee('Ruido en el motor');
});

test('validates motorcycle ownership active mechanic and initial status', function () {
    $otherVehicle = Vehicle::create([...$this->vehicle->only(['vehicle_brand_id', 'vehicle_type_id', 'model', 'year', 'active']), 'license_plate' => 'M999999', 'customer_id' => $this->otherCustomer->id, 'created_by' => $this->admin->id]);
    $this->post(route('orders.store'), [...$this->orderData, 'vehicle_id' => $otherVehicle->id])->assertSessionHasErrors('vehicle_id');
    $seller = User::factory()->create();
    $seller->forceFill(['role_id' => 3])->save();
    $this->post(route('orders.store'), [...$this->orderData, 'mechanic_id' => $seller->id])->assertSessionHasErrors('mechanic_id');
    $this->post(route('orders.store'), [...$this->orderData, 'order_status_id' => OrderStatus::where('name', 'Entregado')->value('id')])->assertSessionHasErrors('order_status_id');
    $this->post(route('orders.store'), [...$this->orderData, 'order_status_id' => OrderStatus::where('name', 'Cancelada')->value('id')])->assertSessionHasErrors('order_status_id');
    expect(Order::count())->toBe(0);
});

test('adds edits and removes parts with atomic inventory movements and price snapshots', function () {
    $order = createTestOrder($this);
    $this->post(route('orders.items.store', $order), ['spare_part_id' => $this->part->id, 'quantity' => 3])->assertSessionHasNoErrors();
    $item = OrderItem::firstOrFail();
    expect($this->part->fresh()->stock_quantity)->toBe(7)->and($item->unit_price)->toBe('2500.00')->and($item->line_total)->toBe('7500.00');
    $this->part->update(['price' => 3000]);
    $this->put(route('orders.items.update', [$order, $item]), ['spare_part_id' => $this->part->id, 'quantity' => 5])->assertSessionHasNoErrors();
    expect($this->part->fresh()->stock_quantity)->toBe(5)->and($item->fresh()->unit_price)->toBe('2500.00')->and($item->fresh()->line_total)->toBe('12500.00');
    $this->put(route('orders.items.update', [$order, $item]), ['spare_part_id' => $this->part->id, 'quantity' => 2])->assertSessionHasNoErrors();
    expect($this->part->fresh()->stock_quantity)->toBe(8);
    $this->delete(route('orders.items.destroy', [$order, $item]))->assertSessionHasNoErrors();
    expect($this->part->fresh()->stock_quantity)->toBe(10)->and(OrderItem::count())->toBe(0)->and(InventoryMovement::orderBy('id')->pluck('type')->all())->toBe(['OUT', 'OUT', 'IN', 'IN']);
});

test('rejects duplicate parts insufficient inventory and cross order item access without changing stock', function () {
    $order = createTestOrder($this);
    $this->post(route('orders.items.store', $order), ['spare_part_id' => $this->part->id, 'quantity' => 9])->assertSessionHasNoErrors();
    $item = OrderItem::firstOrFail();
    $this->post(route('orders.items.store', $order), ['spare_part_id' => $this->part->id, 'quantity' => 1])->assertSessionHasErrors('spare_part_id');
    $this->put(route('orders.items.update', [$order, $item]), ['spare_part_id' => $this->part->id, 'quantity' => 11])->assertSessionHasErrors('quantity');
    $second = Order::create([...$this->orderData, 'number' => 'OT-2026-999999', 'created_by' => $this->admin->id]);
    $this->put(route('orders.items.update', [$second, $item]), ['spare_part_id' => $this->part->id, 'quantity' => 1])->assertNotFound();
    expect($this->part->fresh()->stock_quantity)->toBe(1)->and($item->fresh()->quantity)->toBe(9);
});

test('records status history and locks final orders', function () {
    $order = createTestOrder($this);
    $repair = OrderStatus::where('name', 'En reparación')->firstOrFail();
    $this->put(route('orders.update', $order), [...$this->orderData, 'order_status_id' => $repair->id])->assertSessionHasErrors('status_notes');
    $this->put(route('orders.update', $order), [...$this->orderData, 'order_status_id' => $repair->id, 'status_notes' => 'Diagnóstico terminado'])->assertSessionHasNoErrors();
    expect(OrderHistory::count())->toBe(2)->and($order->fresh()->status->name)->toBe('En reparación');
    $delivered = OrderStatus::where('name', 'Entregado')->firstOrFail();
    $this->put(route('orders.update', $order), [...$this->orderData, 'order_status_id' => $delivered->id, 'status_notes' => 'Entregada al cliente'])->assertSessionHasNoErrors();
    expect($order->fresh()->delivered_at)->not->toBeNull();
    $this->get(route('orders.edit', $order))->assertStatus(409);
    $this->put(route('orders.update', $order), [...$this->orderData, 'order_status_id' => $delivered->id, 'status_notes' => null])->assertSessionHasErrors('order');
    $this->post(route('orders.items.store', $order), ['spare_part_id' => $this->part->id, 'quantity' => 1])->assertSessionHasErrors('order');
});

test('cancelling returns inventory once and preserves order details', function () {
    $order = createTestOrder($this);
    $this->post(route('orders.items.store', $order), ['spare_part_id' => $this->part->id, 'quantity' => 4]);
    $this->delete(route('orders.destroy', $order))->assertSessionHasNoErrors();
    expect($order->fresh()->status->name)->toBe('Cancelada')->and($this->part->fresh()->stock_quantity)->toBe(10)->and(OrderItem::count())->toBe(1)->and(OrderHistory::count())->toBe(2);
    $this->delete(route('orders.destroy', $order))->assertSessionHasErrors('order');
    expect($this->part->fresh()->stock_quantity)->toBe(10);
});

test('search filters and permissions are enforced', function () {
    $order = createTestOrder($this);
    $this->get(route('orders.index', ['search' => 'M123456']))->assertSuccessful()->assertSee($order->number);
    $this->get(route('orders.index', ['status' => $this->received->id, 'mechanic' => $this->mechanic->id]))->assertSee($order->number);
    $this->get(route('orders.index', ['from' => '2030-01-01']))->assertDontSee($order->number);
    $auditor = User::factory()->create();
    $auditor->forceFill(['role_id' => 4])->save();
    $this->actingAs($auditor);
    $this->get(route('orders.index'))->assertSuccessful()->assertDontSee('+ Nueva orden');
    $this->get(route('orders.show', $order))->assertSuccessful()->assertDontSee('Editar orden')->assertDontSee('Agregar');
    $this->get(route('orders.create'))->assertForbidden();
    $this->put(route('orders.update', $order), $this->orderData)->assertForbidden();
    $this->post(route('orders.items.store', $order), ['spare_part_id' => $this->part->id, 'quantity' => 1])->assertForbidden();
    $this->delete(route('orders.destroy', $order))->assertForbidden();
});
