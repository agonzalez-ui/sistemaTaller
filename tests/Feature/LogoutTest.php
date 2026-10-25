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
    $this->get('/auth/register')->assertNotFound();
});

test('public registration is disabled by default', function () {
    $this->post('/auth/register', [
        'name' => 'Cuenta de prueba',
        'email' => 'registro@example.com',
        'password' => 'ClaveSegura123',
        'password_confirmation' => 'ClaveSegura123',
    ])->assertNotFound();

    $this->assertDatabaseMissing('users', ['email' => 'registro@example.com']);
});

test('web responses include defensive browser headers', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});
