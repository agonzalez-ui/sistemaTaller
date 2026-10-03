<?php

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderStatus;
use App\Models\SparePart;
use App\Models\SparePartBrand;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use Database\Seeders\SimrhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SimrhSeeder::class);
    $this->admin = roleWorkflowUser(1, 'Administrador Integral');
    $this->mechanic = roleWorkflowUser(2, 'Mecánico Integral');
    $this->seller = roleWorkflowUser(3, 'Recepción Integral');
    $this->auditor = roleWorkflowUser(4, 'Auditor Integral');
});

function roleWorkflowUser(int $roleId, string $name): User
{
    $user = User::factory()->create(['name' => $name]);
    $user->forceFill(['role_id' => $roleId])->save();

    return $user;
}

test('every role reaches only its assigned modules through real routes', function () {
    $operationalRoutes = [
        'customers.index', 'vehicles.index', 'orders.index', 'spareparts.index',
        'invoices.index', 'reports.index',
    ];
    $expected = [
        1 => array_fill_keys($operationalRoutes, true),
        2 => ['customers.index' => true, 'vehicles.index' => true, 'orders.index' => true, 'spareparts.index' => true, 'invoices.index' => false, 'reports.index' => false],
        3 => ['customers.index' => true, 'vehicles.index' => true, 'orders.index' => true, 'spareparts.index' => true, 'invoices.index' => true, 'reports.index' => false],
        4 => array_fill_keys($operationalRoutes, true),
    ];

    foreach ([$this->admin, $this->mechanic, $this->seller, $this->auditor] as $user) {
        $this->actingAs($user);
        foreach ($expected[$user->role_id] as $route => $allowed) {
            $response = $this->get(route($route));
            $allowed ? $response->assertSuccessful() : $response->assertForbidden();
        }

        $usersResponse = $this->get(route('users.index'));
        $rolesResponse = $this->get(route('roles.index'));
        $user->role_id === 1
            ? [$usersResponse->assertSuccessful(), $rolesResponse->assertSuccessful()]
            : [$usersResponse->assertForbidden(), $rolesResponse->assertForbidden()];
    }
});

test('administrator mechanic reception and auditor complete a protected workshop workflow', function () {
    $this->actingAs($this->seller)->post(route('customers.store'), [
        'name' => 'Cliente Flujo Completo',
        'identification_number' => '116380560',
        'phone' => '85649860',
        'email' => 'cliente.flujo@example.com',
        'address' => 'San José, Costa Rica',
    ])->assertSessionHasNoErrors();
    $customer = Customer::where('identification_number', '116380560')->firstOrFail();

    $this->post(route('vehicles.store'), [
        'license_plate' => 'M-FLUJO1',
        'customer_id' => $customer->id,
        'vehicle_brand_id' => VehicleBrand::where('name', 'Honda')->value('id'),
        'model' => 'CB190R',
        'year' => 2025,
        'color' => 'Negro',
        'active' => 1,
    ])->assertSessionHasNoErrors();
    $vehicle = Vehicle::where('license_plate', 'M-FLUJO1')->firstOrFail();

    $this->actingAs($this->admin)->post(route('spareparts.store'), [
        'code' => 'REP-FLUJO-01',
        'name' => 'Filtro de aceite de prueba',
        'description' => 'Repuesto utilizado en la prueba integral.',
        'spare_part_brand_id' => SparePartBrand::where('name', 'Generico')->value('id'),
        'price' => 2500,
        'minimum_quantity' => 3,
        'active' => 1,
    ])->assertSessionHasNoErrors();
    $part = SparePart::where('code', 'REP-FLUJO-01')->firstOrFail();

    $this->actingAs($this->mechanic)->post(route('spareparts.movements.store', $part), [
        'type' => 'IN',
        'quantity' => 10,
        'notes' => 'Compra inicial para la prueba integral.',
        'request_token' => (string) Str::uuid(),
    ])->assertSessionHasNoErrors();

    $received = OrderStatus::where('name', 'Recibido')->firstOrFail();
    $orderData = [
        'customer_id' => $customer->id,
        'vehicle_id' => $vehicle->id,
        'order_status_id' => $received->id,
        'mechanic_id' => $this->mechanic->id,
        'description' => 'Cambio de aceite y revisión general.',
        'diagnosis' => null,
        'labor_cost' => 15000,
        'received_at' => '2026-10-03T08:00',
    ];
    $this->actingAs($this->seller)->post(route('orders.store'), $orderData)->assertSessionHasNoErrors();
    $order = Order::firstOrFail();

    $this->actingAs($this->mechanic)->post(route('orders.items.store', $order), [
        'spare_part_id' => $part->id,
        'quantity' => 2,
    ])->assertSessionHasNoErrors();

    foreach ([
        ['En diagnóstico', 'Se inició la revisión de la motocicleta.', null],
        ['En reparación', 'Se autorizó y comenzó el mantenimiento.', 'Filtro deteriorado y aceite fuera de vida útil.'],
        ['Listo', 'Trabajo terminado y funcionamiento verificado.', 'Se reemplazó el filtro, se cambió el aceite y se realizó una prueba final.'],
    ] as [$statusName, $notes, $diagnosis]) {
        $this->put(route('orders.update', $order), [
            ...$orderData,
            'order_status_id' => OrderStatus::where('name', $statusName)->value('id'),
            'diagnosis' => $diagnosis,
            'status_notes' => $notes,
        ])->assertSessionHasNoErrors();
        $order->refresh();
    }

    $this->actingAs($this->seller)->post(route('invoices.store'), [
        'order_id' => $order->id,
        'discount' => 0,
        'tax_rate' => 13,
    ])->assertSessionHasNoErrors();
    $invoice = Invoice::firstOrFail();
    $this->post(route('orders.deliver', $order))->assertSessionHasNoErrors();

    expect($customer->phones()->value('phone'))->toBe('85649860')
        ->and($vehicle->customer_id)->toBe($customer->id)
        ->and($part->fresh()->stock_quantity)->toBe(8)
        ->and(InventoryMovement::orderBy('id')->pluck('type')->all())->toBe(['IN', 'OUT'])
        ->and($order->fresh()->status->name)->toBe('Entregado')
        ->and($order->fresh()->delivered_at)->not->toBeNull()
        ->and(OrderHistory::where('order_id', $order->id)->count())->toBe(5)
        ->and($invoice->subtotal)->toBe('20000.00')
        ->and($invoice->tax_amount)->toBe('2600.00')
        ->and($invoice->total)->toBe('22600.00');

    $this->actingAs($this->auditor);
    $this->get(route('customers.index', ['search' => 'Flujo Completo']))->assertSuccessful()->assertSee($customer->name)->assertDontSee('+ Nuevo cliente');
    $this->get(route('orders.show', $order))->assertSuccessful()->assertSee($order->number)->assertSee('Entregado')->assertDontSee('Editar orden');
    $this->get(route('invoices.show', $invoice))->assertSuccessful()->assertSee($invoice->number)->assertSee('₡22.600,00')->assertDontSee('Anular factura');
    $this->get(route('reports.orders'))->assertSuccessful()->assertSee($order->number);
    $this->post(route('orders.deliver', $order))->assertForbidden();
    $this->delete(route('invoices.destroy', $invoice), ['cancellation_reason' => 'Intento no autorizado'])->assertForbidden();
});

test('direct mutation attempts are blocked for mechanic reception and auditor according to the matrix', function () {
    $customer = Customer::create(['name' => 'Cliente protegido', 'identification_number' => '116380560', 'created_by' => $this->admin->id]);
    $part = SparePart::create(['code' => 'PROT-001', 'name' => 'Repuesto protegido', 'price' => 1000, 'stock_quantity' => 1, 'minimum_quantity' => 0, 'active' => true, 'created_by' => $this->admin->id]);

    $this->actingAs($this->mechanic);
    $this->put(route('customers.update', $customer), ['name' => 'Alterado'])->assertForbidden();
    $this->get(route('invoices.index'))->assertForbidden();
    $this->get(route('reports.index'))->assertForbidden();

    $this->actingAs($this->seller);
    $this->delete(route('customers.destroy', $customer))->assertForbidden();
    $this->get(route('reports.index'))->assertForbidden();
    $this->post(route('spareparts.store'), [])->assertForbidden();

    $this->actingAs($this->auditor);
    $this->post(route('customers.store'), [])->assertForbidden();
    $this->post(route('orders.store'), [])->assertForbidden();
    $this->post(route('invoices.store'), [])->assertForbidden();
    $this->post(route('spareparts.movements.store', $part), [])->assertForbidden();

    expect($customer->fresh()->name)->toBe('Cliente protegido');
});
