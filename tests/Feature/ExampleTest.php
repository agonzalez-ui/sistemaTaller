<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the home page redirects guests to login and authenticated users to the dashboard', function () {
    $this->get('/')->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());
    $this->get('/')->assertRedirect(route('dashboard'));
});
