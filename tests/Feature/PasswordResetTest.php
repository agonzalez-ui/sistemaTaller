<?php

use App\Models\User;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('active user can request and complete a password reset', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'usuario@example.com', 'password' => 'Anterior123']);

    $this->get(route('password.request'))->assertSuccessful()->assertSee('Recuperar contraseña');
    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHas('success');

    $token = null;
    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertSuccessful()->assertSee('Nueva contraseña');
    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NuevaClave123',
        'password_confirmation' => 'NuevaClave123',
    ])->assertRedirect(route('login'))->assertSessionHas('success');

    expect(Hash::check('NuevaClave123', $user->fresh()->password))->toBeTrue();
});

test('password recovery does not reveal unknown or inactive accounts', function () {
    Notification::fake();
    $inactive = User::factory()->create(['active' => false]);

    $unknown = $this->post(route('password.email'), ['email' => 'desconocido@example.com']);
    $inactiveResponse = $this->post(route('password.email'), ['email' => $inactive->email]);

    $unknown->assertSessionHas('success');
    $inactiveResponse->assertSessionHas('success');
    expect(session('success'))->toBe('Si existe una cuenta activa con ese correo, recibirá un enlace para restablecer la contraseña.');
    Notification::assertNothingSent();
});

test('password reset rejects weak passwords and invalid tokens', function () {
    $user = User::factory()->create();

    $this->post(route('password.update'), [
        'token' => 'token-invalido',
        'email' => $user->email,
        'password' => '1234',
        'password_confirmation' => '1234',
    ])->assertSessionHasErrors('password');

    $this->post(route('password.update'), [
        'token' => 'token-invalido',
        'email' => $user->email,
        'password' => 'ClaveSegura123',
        'password_confirmation' => 'ClaveSegura123',
    ])->assertSessionHasErrors('email');
});
