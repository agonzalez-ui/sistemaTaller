<?php

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DemoDataSeeder::class);
    $this->actingAs(User::where('email', 'manuel@simrh.test')->firstOrFail());
});

test('motorcycles are paginated in groups of six', function () {
    $firstPage = $this->get(route('vehicles.index'))->assertSuccessful()->viewData('vehicles');
    $secondPage = $this->get(route('vehicles.index', ['page' => 2]))->assertSuccessful()->viewData('vehicles');

    expect($firstPage->perPage())->toBe(6)
        ->and($firstPage->total())->toBe(8)
        ->and($firstPage->lastPage())->toBe(2)
        ->and($firstPage->count())->toBe(6)
        ->and($secondPage->count())->toBe(2);
});

test('spare parts are paginated in groups of six', function () {
    $firstPage = $this->get(route('spareparts.index'))->assertSuccessful()->viewData('parts');
    $secondPage = $this->get(route('spareparts.index', ['page' => 2]))->assertSuccessful()->viewData('parts');

    expect($firstPage->perPage())->toBe(6)
        ->and($firstPage->total())->toBe(12)
        ->and($firstPage->lastPage())->toBe(2)
        ->and($firstPage->count())->toBe(6)
        ->and($secondPage->count())->toBe(6);
});
