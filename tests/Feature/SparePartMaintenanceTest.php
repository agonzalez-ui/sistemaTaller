<?php

use App\Models\InventoryMovement;
use App\Models\SparePart;
use App\Models\SparePartBrand;
use App\Models\User;
use Database\Seeders\SimrhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(SimrhSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['role_id' => 1])->save();
    $this->actingAs($this->admin);
    $this->data = ['code' => ' oil-001 ', 'name' => 'Aceite para moto', 'description' => 'Aceite 10W40', 'spare_part_brand_id' => SparePartBrand::first()->id, 'price' => '6500.50', 'minimum_quantity' => 3, 'active' => 1];
});
function inventoryTestPart($test): SparePart
{
    return SparePart::create([...$test->data, 'code' => 'OIL-001', 'stock_quantity' => 0, 'created_by' => $test->admin->id]);
}
function inventoryTestMovement(string $type, int $quantity): array
{
    return ['type' => $type, 'quantity' => $quantity, 'notes' => 'Referencia de prueba', 'request_token' => (string) Str::uuid()];
}
test('spare part catalog creates edits searches and preserves stock against forged form fields', function () {
    $this->get(route('spareparts.index'))->assertSuccessful();
    $this->get(route('spareparts.create'))->assertSuccessful();
    $this->post(route('spareparts.store'), [...$this->data, 'stock_quantity' => 999, 'created_by' => 999])->assertSessionHasNoErrors();
    $part = SparePart::firstOrFail();
    expect($part->code)->toBe('OIL-001')->and($part->stock_quantity)->toBe(0)->and($part->created_by)->toBe($this->admin->id);
    $this->get(route('spareparts.show', $part))->assertSuccessful()->assertSee('Aceite para moto');
    $this->get(route('spareparts.edit', $part))->assertSuccessful();
    $this->put(route('spareparts.update', $part), [...$this->data, 'name' => 'Aceite actualizado', 'stock_quantity' => 888])->assertSessionHasNoErrors();
    expect($part->fresh()->name)->toBe('Aceite actualizado')->and($part->fresh()->stock_quantity)->toBe(0);
    $this->get(route('spareparts.index', ['search' => 'actualizado']))->assertSee('Aceite actualizado');
    $this->get(route('spareparts.index', ['stock' => 'empty']))->assertSee('Aceite actualizado');
    $this->post(route('spareparts.store'), $this->data)->assertSessionHasErrors('code');
    $this->post(route('spareparts.store'), [...$this->data, 'code' => 'NEW', 'price' => -1, 'minimum_quantity' => -1])->assertSessionHasErrors(['price', 'minimum_quantity']);
    $this->post(route('spareparts.store'), [...$this->data, 'code' => 'NEW', 'price' => '2.555'])->assertSessionHasErrors('price');
});
test('entries exits and physical count adjustments retain correct ledger balances and cannot replay', function () {
    $part = inventoryTestPart($this);
    $entry = inventoryTestMovement('IN', 10);
    $this->post(route('spareparts.movements.store', $part), $entry)->assertSessionHasNoErrors()->assertRedirect(route('spareparts.show', $part));
    $this->post(route('spareparts.movements.store', $part), $entry)->assertSessionHasNoErrors();
    expect($part->fresh()->stock_quantity)->toBe(10)->and(InventoryMovement::count())->toBe(1);
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('OUT', 4))->assertSessionHasNoErrors();
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('ADJUST', 2))->assertSessionHasNoErrors();
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('ADJUST', 0))->assertSessionHasNoErrors();
    expect($part->fresh()->stock_quantity)->toBe(0)->and(InventoryMovement::orderBy('id')->get()->map(fn ($m) => [$m->previous_balance, $m->new_balance])->all())->toBe([[0, 10], [10, 6], [6, 2], [2, 0]]);
    $this->get(route('spareparts.show', $part))->assertSee('Referencia de prueba')->assertSee($this->admin->name);
    $this->get(route('spareparts.show', [$part, 'type' => 'OUT']))->assertSuccessful()->assertViewHas('movements', fn ($m) => $m->total() === 1);
    $this->get(route('spareparts.show', [$part, 'to' => now()->format('Y-m-d')]))->assertSuccessful()->assertViewHas('movements', fn ($m) => $m->total() === 4);
    $this->get(route('spareparts.show', [$part, 'from' => '2000-01-01', 'to' => '2000-01-02']))->assertSee('No hay movimientos');
});
test('insufficient stock and invalid movements roll back both stock and ledger', function () {
    $part = inventoryTestPart($this);
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('OUT', 1))->assertSessionHasErrors('quantity');
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('IN', 0))->assertSessionHasErrors('quantity');
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('IN', -1))->assertSessionHasErrors('quantity');
    $this->post(route('spareparts.movements.store', $part), [...inventoryTestMovement('IN', 1), 'notes' => ''])->assertSessionHasErrors('notes');
    $this->post(route('spareparts.movements.store', $part), [...inventoryTestMovement('ADJUST', 0), 'quantity' => '0'])->assertSessionHasErrors('quantity');
    expect($part->fresh()->stock_quantity)->toBe(0)->and(InventoryMovement::count())->toBe(0);
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('IN', 5))->assertSessionHasNoErrors();
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('OUT', 6))->assertSessionHasErrors('quantity');
    expect($part->fresh()->stock_quantity)->toBe(5)->and(InventoryMovement::count())->toBe(1);
});
test('deactivation preserves inventory and blocks movements until reactivated', function () {
    $part = inventoryTestPart($this);
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('IN', 4));
    $this->delete(route('spareparts.destroy', $part))->assertRedirect(route('spareparts.index'));
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('OUT', 1))->assertSessionHasErrors('quantity');
    expect($part->fresh()->active)->toBeFalse()->and($part->fresh()->stock_quantity)->toBe(4)->and(InventoryMovement::count())->toBe(1);
    $this->get(route('spareparts.index', ['status' => 'inactive']))->assertSee('Aceite para moto');
    $this->put(route('spareparts.update', $part), $this->data)->assertSessionHasNoErrors();
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('OUT', 1))->assertSessionHasNoErrors();
    expect($part->fresh()->stock_quantity)->toBe(3);
});
test('sales can consult stock mechanics can move it and only administrators can adjust or manage brands', function () {
    $part = inventoryTestPart($this);
    foreach ([3, 4] as $role) {
        $user = User::factory()->create();
        $user->forceFill(['role_id' => $role])->save();
        $this->actingAs($user);
        $this->get(route('spareparts.index'))->assertSuccessful()->assertDontSee('+ Nuevo repuesto');
        $this->get(route('spareparts.show', $part))->assertSuccessful()->assertDontSee('Registrar movimiento');
        $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('IN', 1))->assertForbidden();
        $this->get(route('spareparts.create'))->assertForbidden();
        $this->put(route('spareparts.update', $part), $this->data)->assertForbidden();
    }
    $mechanic = User::factory()->create();
    $mechanic->forceFill(['role_id' => 2])->save();
    $this->actingAs($mechanic);
    $this->get(route('spareparts.show', $part))->assertSee('Registrar movimiento')->assertDontSee('Ajuste por conteo');
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('IN', 3))->assertSessionHasNoErrors();
    $this->post(route('spareparts.movements.store', $part), inventoryTestMovement('ADJUST', 7))->assertForbidden();
    $this->delete(route('spareparts.destroy', $part))->assertForbidden();
    $this->get(route('spare-part-brands.index'))->assertForbidden();
    expect($part->fresh()->stock_quantity)->toBe(3);
});
test('spare part brands can be maintained and inactive brands remain on existing parts', function () {
    $this->get(route('spare-part-brands.index'))->assertSuccessful();
    $this->get(route('spare-part-brands.create'))->assertSuccessful();
    $this->post(route('spare-part-brands.store'), ['name' => ' Nueva Marca ', 'active' => 1])->assertSessionHasNoErrors();
    $brand = SparePartBrand::where('name', 'Nueva Marca')->firstOrFail();
    $this->post(route('spare-part-brands.store'), ['name' => 'nueva marca', 'active' => 1])->assertSessionHasErrors('name');
    $this->get(route('spare-part-brands.edit', $brand))->assertSuccessful();
    $part = inventoryTestPart($this);
    $part->update(['spare_part_brand_id' => $brand->id]);
    $this->delete(route('spare-part-brands.destroy', $brand))->assertRedirect();
    $this->get(route('spareparts.create'))->assertDontSee('Nueva Marca');
    $this->get(route('spareparts.edit', $part))->assertSee('Nueva Marca');
    $this->put(route('spareparts.update', $part), [...$this->data, 'spare_part_brand_id' => $brand->id])->assertSessionHasNoErrors();
    $this->post(route('spareparts.store'), [...$this->data, 'code' => 'NEW', 'spare_part_brand_id' => $brand->id])->assertSessionHasErrors('spare_part_brand_id');
    $this->put(route('spare-part-brands.update', $brand), ['name' => 'Nueva Marca', 'active' => 1])->assertSessionHasNoErrors();
    expect($part->fresh()->spare_part_brand_id)->toBe($brand->id)->and($brand->fresh()->active)->toBeTrue();
});
