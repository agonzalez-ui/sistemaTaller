<?php

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\SimrhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SimrhSeeder::class);
});

test('updates a customer and the displayed phone then redirects to the list', function () {
    $user = User::factory()->create();
    $user->forceFill(['role_id' => 1])->save();
    $customer = Customer::create(['name' => 'Original', 'identification_number' => '116380560', 'created_by' => $user->id]);
    $phone = $customer->phones()->create(['phone' => '85649860']);
    $otherPhone = $customer->phones()->create(['phone' => '88888888']);
    $data = ['name' => 'Actualizado', 'identification_number' => '116380560', 'phone' => '87654321', 'email' => 'test@example.com', 'address' => 'San José'];

    $this->actingAs($user)->put(route('customers.update', $customer), $data)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('customers.index'))
        ->assertSessionHas('success', 'Cliente actualizado correctamente.');

    expect($customer->fresh()->name)->toBe('Actualizado')
        ->and($customer->fresh()->created_by)->toBe($user->id)
        ->and($phone->fresh()->phone)->toBe('87654321')
        ->and($otherPhone->fresh()->phone)->toBe('88888888');

    $data['phone'] = '';
    $this->put(route('customers.update', $customer), $data)->assertSessionHasNoErrors()->assertRedirect(route('customers.index'));
    expect($phone->fresh())->toBeNull()
        ->and($otherPhone->fresh()->phone)->toBe('88888888');
});

test('rejects duplicate identification and invalid numbers without changing the customer', function () {
    $user = User::factory()->create();
    $user->forceFill(['role_id' => 1])->save();
    $customer = Customer::create(['name' => 'Original', 'identification_number' => '116380560', 'created_by' => $user->id]);
    Customer::create(['name' => 'Otro', 'identification_number' => '116380561', 'created_by' => $user->id]);
    $this->actingAs($user)->put(route('customers.update', $customer), ['name' => 'Cambio', 'identification_number' => '116380561'])
        ->assertSessionHasErrors('identification_number');
    $this->put(route('customers.update', $customer), ['name' => 'Cambio', 'identification_number' => 'ABC123456', 'phone' => '123ABC45'])
        ->assertSessionHasErrors(['identification_number', 'phone']);
    expect($customer->fresh()->name)->toBe('Original');
});
