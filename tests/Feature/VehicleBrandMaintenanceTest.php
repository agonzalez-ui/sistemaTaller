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
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['role_id' => 1])->save();
    $this->actingAs($this->admin);
});
test('administrator can maintain brands with case insensitive duplicate validation', function () {
    $this->get(route('vehicle-brands.index'))->assertSuccessful()->assertSee('+ Nueva marca');
    $this->get(route('vehicle-brands.create'))->assertSuccessful();
    $this->post(route('vehicle-brands.store'), ['name' => '  Nueva   Marca  ', 'active' => 1])->assertSessionHasNoErrors()->assertRedirect(route('vehicle-brands.index'));
    $brand = VehicleBrand::where('name', 'Nueva Marca')->firstOrFail();
    $this->post(route('vehicle-brands.store'), ['name' => 'nueva marca', 'active' => 1])->assertSessionHasErrors('name');
    $this->get(route('vehicle-brands.edit', $brand))->assertSuccessful();
    $this->put(route('vehicle-brands.update', $brand), ['name' => 'Nueva Marca Actualizada', 'active' => 1])->assertSessionHasNoErrors();
    $this->delete(route('vehicle-brands.destroy', $brand))->assertRedirect(route('vehicle-brands.index'));
    expect($brand->fresh()->active)->toBeFalse();
    $this->post(route('vehicle-brands.store'), ['name' => 'nueva marca actualizada', 'active' => 1])->assertSessionHasErrors('name');
    $this->put(route('vehicle-brands.update', $brand), ['name' => 'Nueva Marca Actualizada', 'active' => 1])->assertSessionHasNoErrors();
    expect($brand->fresh()->active)->toBeTrue()->and(ActivityLog::where('table_name', 'vehicle_brands')->count())->toBe(4);
    $this->get(route('vehicle-brands.index', ['search' => 'Actualizada']))->assertSee('Nueva Marca Actualizada')->assertDontSee('Yamaha');
    $this->get(route('vehicles.create'))->assertSee('Nueva Marca Actualizada');
});
test('disabling brands preserves existing motorcycles and permits edits without new assignments', function () {
    $customer = Customer::create(['name' => 'Propietario', 'identification_number' => '116380560', 'created_by' => $this->admin->id]);
    $brand = VehicleBrand::where('name', 'Honda')->firstOrFail();
    $data = ['license_plate' => 'M123456', 'customer_id' => $customer->id, 'vehicle_brand_id' => $brand->id, 'model' => 'CB190', 'year' => 2024, 'active' => 1];
    $vehicle = Vehicle::create([...$data, 'created_by' => $this->admin->id, 'vehicle_type_id' => VehicleType::where('name', 'Motocicleta')->value('id')]);
    $this->delete(route('vehicle-brands.destroy', $brand))->assertRedirect();
    expect($vehicle->fresh()->vehicle_brand_id)->toBe($brand->id);
    $this->get(route('vehicles.create'))->assertDontSee('Honda');
    $this->get(route('vehicles.edit', $vehicle))->assertSee('Honda');
    $this->put(route('vehicles.update', $vehicle), [...$data, 'model' => 'CB190R'])->assertSessionHasNoErrors();
    $this->post(route('vehicles.store'), [...$data, 'license_plate' => 'M999999'])->assertSessionHasErrors('vehicle_brand_id');
    $this->get(route('vehicle-brands.index', ['status' => 'inactive']))->assertSee('Honda')->assertDontSee('Yamaha');
});
test('mechanics and auditors cannot manage brand catalog', function () {
    $brand = VehicleBrand::where('name', 'Honda')->firstOrFail();
    foreach ([2, 4] as $role) {
        $user = User::factory()->create();
        $user->forceFill(['role_id' => $role])->save();
        $this->actingAs($user);
        $this->get(route('vehicle-brands.index'))->assertForbidden();
        $this->get(route('vehicle-brands.create'))->assertForbidden();
        $this->post(route('vehicle-brands.store'), ['name' => 'Injected', 'active' => 1])->assertForbidden();
        $this->put(route('vehicle-brands.update', $brand), ['name' => 'Injected', 'active' => 1])->assertForbidden();
        $this->delete(route('vehicle-brands.destroy', $brand))->assertForbidden();
        $this->get(route('vehicles.index'))->assertSuccessful()->assertDontSee('Administrar marcas de motos');
    }
    expect($brand->fresh()->active)->toBeTrue();
});
