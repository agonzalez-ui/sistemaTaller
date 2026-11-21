<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\SparePart;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('presentation data seeder creates a small coherent workshop scenario', function () {
    $this->seed(DemoDataSeeder::class);

    expect(User::count())->toBe(3)
        ->and(Customer::count())->toBe(5)
        ->and(Vehicle::count())->toBe(8)
        ->and(SparePart::count())->toBe(12)
        ->and(Order::count())->toBe(4)
        ->and(Invoice::count())->toBe(1)
        ->and(Order::whereNull('odometer')->count())->toBe(0)
        ->and(Customer::doesntHave('vehicles.orders')->count())->toBe(1);

    $this->assertDatabaseHas('orders', [
        'fuel_level' => 'half',
        'intake_condition' => 'Rayón superficial en el costado derecho; se recibe con una llave y casco.',
    ]);
});
