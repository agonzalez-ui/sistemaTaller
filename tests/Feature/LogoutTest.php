<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('logout clears the session and returns to login', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['workshop_test' => 'value']);
    $this->get(route('dashboard'))->assertSuccessful()->assertSee('Cerrar sesión');
    $this->post(route('logout'))->assertRedirect(route('login'))->assertSessionMissing('workshop_test');
    $this->assertGuest();
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('login'))->assertSuccessful();
    $this->get(route('register'))->assertSuccessful();
});
