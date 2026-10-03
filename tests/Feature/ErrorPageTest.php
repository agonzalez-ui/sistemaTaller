<?php

use Illuminate\Support\Facades\Blade;

test('public missing pages use the branded 404 screen', function () {
    $this->get('/pagina-que-no-existe-en-simrh')
        ->assertNotFound()
        ->assertSee('No encontramos esa página')
        ->assertSee('SIMRH');
});

test('all custom error templates render safe and helpful messages', function () {
    $pages = [
        403 => 'Acceso restringido',
        404 => 'No encontramos esa página',
        419 => 'La sesión venció',
        429 => 'Demasiados intentos',
        500 => 'No pudimos completar la operación',
        503 => 'Sistema en mantenimiento',
    ];

    foreach ($pages as $status => $message) {
        $html = Blade::render("@include('errors.{$status}')");
        expect($html)->toContain((string) $status)->toContain($message)->toContain('Ir al inicio')->not->toContain('Stack trace');
    }
});
