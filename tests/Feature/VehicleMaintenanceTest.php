<?php

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleType;
use Database\Seeders\SimrhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(SimrhSeeder::class);
    $this->owner = User::factory()->create();
    $this->owner->forceFill(['role_id' => 1])->save();
    $this->actingAs($this->owner);
    $this->customer = Customer::create(['name' => 'Santiago Gonzalez Ulate', 'identification_number' => '116380560', 'created_by' => $this->owner->id]);
    $this->data = ['license_plate' => ' m123456 ', 'customer_id' => $this->customer->id, 'vehicle_brand_id' => VehicleBrand::where('name', 'Honda')->value('id'), 'model' => 'CB 190', 'year' => 2024, 'color' => 'Rojo', 'active' => 1];
});
test('register edit deactivate and reactivate a motorcycle while preserving its owner and creator', function () {
    $this->get(route('vehicles.create'))->assertSuccessful()->assertSee($this->customer->name);
    $this->post(route('vehicles.store'), [...$this->data, 'created_by' => 999, 'vehicle_type_id' => 999])->assertSessionHasNoErrors()->assertRedirect(route('vehicles.index'));
    $vehicle = Vehicle::firstOrFail();
    expect($vehicle->license_plate)->toBe('M123456')->and($vehicle->created_by)->toBe($this->owner->id)->and($vehicle->type->name)->toBe('Motocicleta');
    $this->get(route('vehicles.edit', $vehicle))->assertSuccessful()->assertSee('CB 190');
    $this->put(route('vehicles.update', $vehicle), [...$this->data, 'model' => 'CB 190R'])->assertSessionHasNoErrors()->assertRedirect(route('vehicles.index'));
    expect($vehicle->fresh()->model)->toBe('CB 190R');
    $this->delete(route('vehicles.destroy', $vehicle))->assertRedirect(route('vehicles.index'));
    expect($vehicle->fresh()->active)->toBeFalse();
    $this->put(route('vehicles.update', $vehicle), $this->data)->assertSessionHasNoErrors();
    expect($vehicle->fresh()->active)->toBeTrue()->and(ActivityLog::where('table_name', 'vehicles')->count())->toBe(4);
});
test('validation blocks duplicate plates invalid years and inactive owners or brands', function () {
    $this->post(route('vehicles.store'), $this->data)->assertSessionHasNoErrors();
    $this->post(route('vehicles.store'), $this->data)->assertSessionHasErrors('license_plate');
    $this->post(route('vehicles.store'), [...$this->data, 'license_plate' => 'BAD / PLATE', 'year' => now()->year + 2])->assertSessionHasErrors(['license_plate', 'year']);
    $this->customer->update(['active' => false]);
    VehicleBrand::find($this->data['vehicle_brand_id'])->update(['active' => false]);
    $this->post(route('vehicles.store'), [...$this->data, 'license_plate' => 'M999999'])->assertSessionHasErrors(['customer_id', 'vehicle_brand_id']);
    expect(Vehicle::count())->toBe(1);
});
test('search and status filters return motorcycles and exclude other vehicle types', function () {
    $this->post(route('vehicles.store'), $this->data)->assertSessionHasNoErrors();
    Vehicle::create([...$this->data, 'license_plate' => 'CAR001', 'model' => 'Other vehicle', 'vehicle_type_id' => VehicleType::where('name', 'Carro')->value('id'), 'created_by' => $this->owner->id]);
    $this->get(route('vehicles.index', ['search' => 'Santiago']))->assertSuccessful()->assertSee('M123456')->assertDontSee('CAR001');
    $this->get(route('vehicles.index', ['search' => 'Honda']))->assertSee('M123456');
    $this->get(route('vehicles.index', ['status' => 'inactive']))->assertDontSee('M123456');
    $this->get(route('vehicles.index', ['search' => 'Absent']))->assertSee('No hay motos');
    $car = Vehicle::where('license_plate', 'CAR001')->firstOrFail();
    $this->get(route('vehicles.edit', $car))->assertNotFound();
    $this->delete(route('vehicles.destroy', $car))->assertNotFound();
});
test('auditors cannot mutate motorcycles and mechanics cannot deactivate them', function () {
    $this->post(route('vehicles.store'), $this->data);
    $vehicle = Vehicle::firstOrFail();
    $auditor = User::factory()->create();
    $auditor->forceFill(['role_id' => 4])->save();
    $this->actingAs($auditor);
    $this->get(route('vehicles.index'))->assertSuccessful()->assertDontSee('+ Nueva moto')->assertDontSee('Desactivar');
    $this->get(route('vehicles.create'))->assertForbidden();
    $this->post(route('vehicles.store'), $this->data)->assertForbidden();
    $this->put(route('vehicles.update', $vehicle), $this->data)->assertForbidden();
    $this->delete(route('vehicles.destroy', $vehicle))->assertForbidden();
    $mechanic = User::factory()->create();
    $mechanic->forceFill(['role_id' => 2])->save();
    $this->actingAs($mechanic);
    $this->get(route('vehicles.edit', $vehicle))->assertSuccessful();
    $this->put(route('vehicles.update', $vehicle), [...$this->data, 'model' => 'Mechanic update'])->assertSessionHasNoErrors();
    $this->delete(route('vehicles.destroy', $vehicle))->assertForbidden();
    expect($vehicle->fresh()->active)->toBeTrue();
});

test('motorcycle catalog offers motorcycle brands and excludes car brands', function () {
    $this->get(route('vehicles.create'))->assertSuccessful()->assertSee('Yamaha')->assertSee('Bajaj')->assertSee('Honda')->assertDontSee('Toyota')->assertDontSee('Chevrolet');
    expect(VehicleBrand::where('name', 'Yamaha')->where('active', true)->exists())->toBeTrue();
    VehicleBrand::create(['name' => 'Toyota', 'active' => true]);
    $migration = require database_path('migrations/2026_09_17_111456_correct_motorcycle_brand_catalog.php');
    $hondaId = VehicleBrand::where('name', 'Honda')->value('id');
    $migration->up();
    expect(VehicleBrand::where('name', 'Toyota')->firstOrFail()->active)->toBeFalse()->and(VehicleBrand::where('name', 'Honda')->value('id'))->toBe($hondaId);
    $this->get(route('vehicles.create'))->assertDontSee('Toyota');
});
