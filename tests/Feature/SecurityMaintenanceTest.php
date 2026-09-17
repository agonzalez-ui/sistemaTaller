<?php

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\SimrhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SimrhSeeder::class);
    $this->administrator = User::factory()->create();
    $this->administrator->forceFill(['role_id' => 1])->save();
    $this->actingAs($this->administrator);
});

test('administrator can open all security maintenance screens', function () {
    foreach (['users.index', 'users.create', 'roles.index', 'roles.create'] as $route) {
        $this->get(route($route))->assertSuccessful();
    }
    $this->get(route('users.edit', $this->administrator))->assertSuccessful();
    $this->get(route('roles.edit', Role::find(1)))->assertSuccessful()->assertSee('Este rol está protegido');
    $this->get(route('dashboard'))->assertSuccessful()->assertSee('Administración y seguridad');
});

test('administrator creates and updates users without exposing passwords to the audit log', function () {
    $data = ['name' => 'Nuevo Mecánico', 'email' => 'mechanic@example.com', 'role_id' => 2, 'active' => 1, 'verified' => 1, 'password' => 'Taller12345', 'password_confirmation' => 'Taller12345'];
    $this->post(route('users.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('users.index'));
    $user = User::where('email', $data['email'])->firstOrFail();
    expect(Hash::check('Taller12345', $user->password))->toBeTrue()
        ->and($user->role_id)->toBe(2)
        ->and($user->email_verified_at)->not->toBeNull();
    $password = $user->password;
    $data['name'] = 'Mecánico Actualizado';
    $data['password'] = '';
    $data['password_confirmation'] = '';
    $this->put(route('users.update', $user), $data)->assertSessionHasNoErrors()->assertRedirect(route('users.index'));
    expect($user->fresh()->password)->toBe($password)
        ->and($user->fresh()->name)->toBe('Mecánico Actualizado')
        ->and(ActivityLog::where('table_name', 'users')->count())->toBe(2)
        ->and(ActivityLog::pluck('details')->implode(' '))->not->toContain('Taller12345');
    $this->post(route('users.store'), $data)->assertSessionHasErrors('email');
});

test('non administrators cannot manage users or roles even with forged security permissions', function () {
    $user = User::factory()->create();
    $user->forceFill(['role_id' => 2])->save();
    Role::find(2)->modules()->syncWithoutDetaching([Module::where('slug', 'users')->value('id') => ['can_view' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 1]]);
    $this->actingAs($user);
    $this->get(route('users.index'))->assertForbidden();
    $this->post(route('users.store'), [])->assertForbidden();
    $this->get(route('roles.index'))->assertForbidden();
    $this->get(route('dashboard'))->assertSuccessful()->assertDontSee('Administración y seguridad');
});

test('permissions are enforced on customer endpoints and their buttons', function () {
    $customer = Customer::create(['name' => 'Cliente', 'identification_number' => '116380560', 'created_by' => $this->administrator->id]);
    $user = User::factory()->create();
    $user->forceFill(['role_id' => 4])->save();
    $this->actingAs($user);
    $this->get(route('customers.index'))->assertSuccessful()->assertDontSee('+ Nuevo cliente')->assertDontSee('Eliminar');
    $this->get(route('customers.create'))->assertForbidden();
    $this->put(route('customers.update', $customer), ['name' => 'Ataque'])->assertForbidden();
    $this->delete(route('customers.destroy', $customer))->assertForbidden();
    expect($customer->fresh()->name)->toBe('Cliente');
});

test('permission changes take effect and require view access', function () {
    $module = Module::where('slug', 'customers')->firstOrFail();
    $this->post(route('roles.store'), ['name' => 'Consulta de taller', 'description' => 'Solo clientes', 'active' => 1, 'is_administrator' => 1, 'permissions' => [$module->id => ['can_view' => 1, 'can_edit' => 0]]])->assertSessionHasNoErrors()->assertRedirect(route('roles.index'));
    $role = Role::where('name', 'Consulta de taller')->firstOrFail();
    expect($role->is_administrator)->toBeFalse();
    $user = User::factory()->create();
    $user->forceFill(['role_id' => $role->id])->save();
    $this->actingAs($user)->get(route('customers.index'))->assertSuccessful();
    $this->actingAs($this->administrator)->put(route('roles.update', $role), ['name' => $role->name, 'description' => 'Sin acceso', 'active' => 1, 'permissions' => [$module->id => ['can_view' => 0, 'can_edit' => 1]]])->assertSessionHasNoErrors();
    $this->actingAs($user)->get(route('customers.index'))->assertForbidden();
    expect($role->fresh()->modules->firstWhere('id', $module->id)->pivot->can_edit)->toBe(0);
});

test('disabled users and roles lose access on their next request and can be reactivated', function () {
    $user = User::factory()->create();
    $user->forceFill(['role_id' => 2])->save();
    $this->delete(route('users.destroy', $user))->assertRedirect(route('users.index'));
    expect($user->fresh()->active)->toBeFalse();
    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
    $this->actingAs($this->administrator)->put(route('users.update', $user), ['name' => $user->name, 'email' => $user->email, 'role_id' => 2, 'active' => 1, 'verified' => 1])->assertSessionHasNoErrors();
    $this->actingAs($user->fresh())->get(route('customers.index'))->assertSuccessful();
    $this->actingAs($this->administrator)->delete(route('roles.destroy', Role::find(2)))->assertRedirect(route('roles.index'));
    $this->actingAs($user->fresh())->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('administrator account and role cannot be disabled by the same administrator', function () {
    $this->delete(route('users.destroy', $this->administrator))->assertSessionHasErrors('active');
    $this->delete(route('roles.destroy', Role::find(1)))->assertSessionHasErrors('active');
    $this->put(route('users.update', $this->administrator), ['name' => $this->administrator->name, 'email' => $this->administrator->email, 'role_id' => 2, 'active' => 1, 'verified' => 1])->assertSessionHasErrors('active');
    expect($this->administrator->fresh()->active)->toBeTrue()
        ->and($this->administrator->fresh()->role_id)->toBe(1)
        ->and(Role::find(1)->active)->toBeTrue();
});

test('unassigned accounts cannot access the workshop or promote themselves', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard'))->assertSuccessful()->assertSee('pendiente de asignación de rol');
    $this->get(route('customers.index'))->assertForbidden();
    $this->put(route('users.update', $user), ['role_id' => 1])->assertForbidden();
});

test('users and roles can be searched by their maintenance filters', function () {
    $user = User::factory()->create(['name' => 'Julian Rodriguez', 'email' => 'julian@example.com']);
    $user->forceFill(['role_id' => 2])->save();
    $this->get(route('users.index', ['search' => 'julian@example.com', 'role' => 2, 'status' => 'active']))->assertSuccessful()->assertSee('Julian Rodriguez')->assertDontSee($this->administrator->email);
    $this->get(route('roles.index', ['search' => 'Mecánico']))->assertSuccessful()->assertSee('Mecánico')->assertDontSee('Acceso total al sistema.');
});

test('login rejects disabled users and logs successful login and explicit logout', function () {
    $user = User::factory()->create(['password' => 'Taller12345']);
    $user->forceFill(['role_id' => 2, 'active' => false])->save();
    Auth::logout();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Taller12345'])->assertSessionHas('error');
    $this->assertGuest();
    $user->forceFill(['active' => true])->save();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Taller12345'])->assertRedirect(route('dashboard'));
    $this->post(route('logout'))->assertRedirect(route('login'));
    $log = AccessLog::where('user_id', $user->id)->firstOrFail();
    expect($log->logged_out_at)->not->toBeNull()->and($log->logout_type)->toBe('MANUAL');
});
