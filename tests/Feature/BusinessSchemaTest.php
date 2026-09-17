<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\SimrhSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('upgrade preserves authentication and links customers to vehicles', function () {
    expect(DB::connection()->getDriverName())->toBe('sqlite');
    $this->artisan('migrate', ['--path' => 'database/migrations/0001_01_01_000000_create_users_table.php'])->assertSuccessful();
    $user = User::factory()->create();
    $original = $user->getRawOriginal();
    foreach (glob(database_path('migrations/2026_09_15_*.php')) as $migration) {
        $this->artisan('migrate', ['--path' => 'database/migrations/'.basename($migration)])->assertSuccessful();
    }
    $this->artisan('migrate')->assertSuccessful();
    $this->seed(SimrhSeeder::class);
    $counts = [];
    foreach (['roles', 'modules', 'module_role', 'order_statuses', 'vehicle_brands', 'vehicle_types', 'spare_part_brands'] as $table) {
        $counts[$table] = DB::table($table)->count();
    }
    $this->seed(SimrhSeeder::class);
    foreach ($counts as $table => $count) {
        expect(DB::table($table)->count())->toBe($count);
    }
    foreach (['id', 'name', 'email', 'email_verified_at', 'password'] as $field) {
        expect($user->fresh()->getRawOriginal($field))->toBe($original[$field]);
    }
    $customer = Customer::create(['identification_number' => 'TEST-1', 'name' => 'Cliente', 'created_by' => $user->id]);
    $vehicle = Vehicle::create(['license_plate' => 'TEST-01', 'customer_id' => $customer->id, 'vehicle_brand_id' => DB::table('vehicle_brands')->value('id'), 'vehicle_type_id' => DB::table('vehicle_types')->value('id'), 'model' => 'Test', 'year' => 2026, 'created_by' => $user->id]);
    expect($vehicle->customer->id)->toBe($customer->id)
        ->and($customer->creator->id)->toBe($user->id);
    $this->actingAs($user)->get(route('dashboard'))->assertSuccessful();
    $this->artisan('migrate:rollback')->assertSuccessful();
    expect(Schema::hasColumn('customers', 'name'))->toBeFalse()
        ->and(Schema::hasTable('customers'))->toBeTrue()
        ->and($user->fresh()->getRawOriginal('password'))->toBe($original['password']);
});
