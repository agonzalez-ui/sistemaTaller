<?php

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\SimrhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(SimrhSeeder::class);
});
test('all assigned roles can view system information and relevant help', function () {
    foreach ([1, 2, 3, 4] as $role) {
        $user = User::factory()->create();
        $user->forceFill(['role_id' => $role])->save();
        $this->actingAs($user);
        $this->get(route('about'))->assertSuccessful()->assertSee('Manuel Rodriguez')->assertSee('Desarrollador')->assertSee('PHP')->assertSee('MySQL')->assertSee('0.1.0');
        $this->get(route('help'))->assertSuccessful()->assertSee('Repuestos y existencias');
        $this->get(route('dashboard'))->assertSee('Información del sistema')->assertSee(route('about'))->assertSee(route('help'));
    }
});
test('system information requires authentication verification and the corresponding role permission', function () {
    $this->get(route('about'))->assertRedirect(route('login'));
    $this->get(route('help'))->assertRedirect(route('login'));
    $user = User::factory()->unverified()->create();
    $user->forceFill(['role_id' => 4])->save();
    $this->actingAs($user);
    $this->get(route('about'))->assertRedirect(route('verification.notice'));
    $user->forceFill(['email_verified_at' => now()])->save();
    Role::find(4)->modules()->updateExistingPivot(Module::where('slug', 'about')->value('id'), ['can_view' => false]);
    $this->get(route('about'))->assertForbidden();
    $this->get(route('help'))->assertSuccessful();
    $this->get(route('dashboard'))->assertDontSee(route('about'))->assertSee(route('help'));
});
test('help filters administrator instructions and does not claim unfinished features are available', function () {
    $user = User::factory()->create();
    $user->forceFill(['role_id' => 2])->save();
    $this->actingAs($user);
    $this->get(route('help'))->assertSuccessful()->assertDontSee('Marcas, usuarios y permisos')->assertSee('siguen en desarrollo');
    $user->forceFill(['role_id' => 1])->save();
    $this->get(route('help'))->assertSee('Marcas, usuarios y permisos');
});
test('authentication screens retain their original layout without the system footer', function () {
    $this->get(route('login'))->assertSuccessful()->assertDontSee('Información del sistema');
    $this->get(route('register'))->assertSuccessful()->assertDontSee('Información del sistema');
});
