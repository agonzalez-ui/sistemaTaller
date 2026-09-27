<?php

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\SparePart;
use App\Models\Vehicle;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates realistic pagination and filter data without duplicating it', function () {
    $this->seed(DemoDataSeeder::class);

    expect(Customer::count())->toBeGreaterThanOrEqual(36)
        ->and(Vehicle::count())->toBeGreaterThanOrEqual(48)
        ->and(SparePart::count())->toBeGreaterThanOrEqual(30)
        ->and(Order::count())->toBe(60)
        ->and(Invoice::count())->toBeGreaterThan(12)
        ->and(InventoryMovement::count())->toBeGreaterThan(100)
        ->and(AccessLog::count())->toBe(45)
        ->and(ActivityLog::count())->toBe(90)
        ->and(SparePart::whereColumn('stock_quantity', '<=', 'minimum_quantity')->count())->toBeGreaterThan(5);

    $counts = [Customer::count(), Vehicle::count(), SparePart::count(), Order::count(), Invoice::count(), InventoryMovement::count(), AccessLog::count(), ActivityLog::count()];
    $this->seed(DemoDataSeeder::class);

    expect([Customer::count(), Vehicle::count(), SparePart::count(), Order::count(), Invoice::count(), InventoryMovement::count(), AccessLog::count(), ActivityLog::count()])->toBe($counts);
});
