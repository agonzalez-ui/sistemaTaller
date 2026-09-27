<?php

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\SparePart;
use App\Models\SparePartBrand;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleType;
use Database\Seeders\SimrhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SimrhSeeder::class);
    $this->admin = User::factory()->create(['name' => 'Administrador Reportes']);
    $this->admin->forceFill(['role_id' => 1])->save();
    $this->mechanic = User::factory()->create(['name' => 'Mecánico Reportes']);
    $this->mechanic->forceFill(['role_id' => 2])->save();
    $this->customer = Customer::create(['name' => 'Cliente Reportes', 'identification_number' => '116380560', 'created_by' => $this->admin->id]);
    $this->vehicle = Vehicle::create(['license_plate' => 'M123456', 'customer_id' => $this->customer->id, 'vehicle_brand_id' => VehicleBrand::where('name', 'Honda')->value('id'), 'vehicle_type_id' => VehicleType::where('name', 'Motocicleta')->value('id'), 'model' => 'CB190', 'year' => 2024, 'active' => true, 'created_by' => $this->admin->id]);
    $this->order = Order::create(['number' => 'OT-2026-000001', 'customer_id' => $this->customer->id, 'vehicle_id' => $this->vehicle->id, 'order_status_id' => OrderStatus::where('name', 'Entregado')->value('id'), 'mechanic_id' => $this->mechanic->id, 'description' => 'Trabajo reportado', 'labor_cost' => 10000, 'received_at' => '2026-09-20 08:00:00', 'delivered_at' => '2026-09-21 08:00:00', 'created_by' => $this->admin->id]);
    $this->invoice = Invoice::create(['number' => 'FAC-2026-000001', 'order_id' => $this->order->id, 'customer_id' => $this->customer->id, 'vehicle_id' => $this->vehicle->id, 'date' => '2026-09-21 09:00:00', 'subtotal' => 10000, 'discount' => 0, 'tax_rate' => 13, 'tax_amount' => 1300, 'total' => 11300, 'status' => 'ISSUED', 'created_by' => $this->admin->id]);
    $this->part = SparePart::create(['code' => 'REP-LOW', 'name' => 'Repuesto escaso', 'spare_part_brand_id' => SparePartBrand::where('name', 'Bosch')->value('id'), 'price' => 5000, 'stock_quantity' => 1, 'minimum_quantity' => 2, 'active' => true, 'created_by' => $this->admin->id]);
    AccessLog::create(['user_id' => $this->admin->id, 'logged_in_at' => '2026-09-22 08:00:00', 'logged_out_at' => '2026-09-22 09:00:00', 'logout_type' => 'MANUAL', 'ip_address' => '127.0.0.1']);
    ActivityLog::create(['user_id' => $this->admin->id, 'occurred_at' => '2026-09-22 08:30:00', 'action' => 'UPDATE', 'table_name' => 'orders', 'record_id' => (string) $this->order->id, 'details' => 'Orden actualizada para la prueba.', 'ip_address' => '127.0.0.1']);
    $this->actingAs($this->admin);
});

test('renders the five required dynamic reports with headers totals and footers', function () {
    $this->get(route('reports.index'))->assertSuccessful()->assertSee('Facturación')->assertSee('Ingresos y salidas')->assertSee('Movimientos de usuarios');
    $this->get(route('reports.billing'))->assertSuccessful()->assertSee('FAC-2026-000001')->assertSee('Total emitido')->assertSee('Fin del reporte');
    $this->get(route('reports.orders'))->assertSuccessful()->assertSee('OT-2026-000001')->assertSee('Valor registrado')->assertSee('Fin del reporte');
    $this->get(route('reports.inventory'))->assertSuccessful()->assertSee('REP-LOW')->assertSee('Valor del inventario')->assertSee('Fin del reporte');
    $this->get(route('reports.access'))->assertSuccessful()->assertSee('Administrador Reportes')->assertSee('Sesiones cerradas')->assertSee('Fin del reporte');
    $this->get(route('reports.activity'))->assertSuccessful()->assertSee('Orden actualizada para la prueba.')->assertSee('Tipos de movimiento')->assertSee('Fin del reporte');
});

test('applies business report filters and validates date ranges', function () {
    $this->get(route('reports.billing', ['customer' => $this->customer->id, 'status' => 'ISSUED', 'from' => '2026-09-01', 'to' => '2026-09-30']))->assertSee($this->invoice->number);
    $this->get(route('reports.billing', ['status' => 'CANCELLED']))->assertDontSee($this->invoice->number);
    $this->get(route('reports.orders', ['mechanic' => $this->mechanic->id, 'status' => $this->order->order_status_id]))->assertSee($this->order->number);
    $this->get(route('reports.orders', ['from' => '2027-01-01']))->assertDontSee($this->order->number);
    $this->get(route('reports.inventory', ['brand' => $this->part->spare_part_brand_id, 'stock' => 'low', 'state' => 'active']))->assertSee($this->part->code);
    $this->get(route('reports.inventory', ['stock' => 'out']))->assertDontSee($this->part->code);
    $this->get(route('reports.billing', ['from' => '2026-09-30', 'to' => '2026-09-01']))->assertSessionHasErrors('to');
});

test('filters security reports by user date and movement type', function () {
    $this->get(route('reports.access', ['user' => $this->admin->id, 'from' => '2026-09-22', 'to' => '2026-09-22']))->assertSee('127.0.0.1');
    $this->get(route('reports.access', ['from' => '2027-01-01']))->assertDontSee('127.0.0.1');
    $this->get(route('reports.activity', ['user' => $this->admin->id, 'action' => 'UPDATE']))->assertSee('Orden actualizada para la prueba.');
    $this->get(route('reports.activity', ['action' => 'DELETE']))->assertDontSee('Orden actualizada para la prueba.');
});

test('allows administrator and auditor reports while denying operational roles', function () {
    $auditor = User::factory()->create();
    $auditor->forceFill(['role_id' => 4])->save();
    $this->actingAs($auditor);
    $this->get(route('reports.index'))->assertSuccessful();
    $this->get(route('reports.activity'))->assertSuccessful();
    $this->actingAs($this->mechanic);
    $this->get(route('reports.index'))->assertForbidden();
    $this->get(route('reports.billing'))->assertForbidden();
});

test('paginates large reports and exports a branded excel workbook', function () {
    foreach (range(1, 55) as $position) {
        ActivityLog::create(['user_id' => $this->admin->id, 'occurred_at' => now()->addSeconds($position), 'action' => 'UPDATE', 'table_name' => 'customers', 'record_id' => 'P-'.$position, 'details' => 'Registro de paginación '.$position]);
    }
    $this->get(route('reports.activity'))->assertSuccessful()->assertViewHas('rows', fn ($rows) => $rows->count() === 50 && $rows->total() === 56)->assertSee('Total filtrado: 56');

    $response = $this->get(route('reports.excel', ['type' => 'billing', 'status' => 'ISSUED']))->assertSuccessful()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $path = $response->baseResponse->getFile()->getPathname();
    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue();
    $names = collect(range(0, $zip->numFiles - 1))->map(fn ($index) => $zip->getNameIndex($index));
    expect($names->contains(fn ($name) => str_starts_with($name, 'xl/media/')))->toBeTrue();
    $zip->close();
});
