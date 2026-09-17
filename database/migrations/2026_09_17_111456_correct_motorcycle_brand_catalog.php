<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('vehicle_brands')->whereIn('name', ['Toyota', 'Nissan', 'Hyundai', 'Kia', 'Mitsubishi', 'Ford', 'Chevrolet', 'Mazda'])->update(['active' => false, 'updated_at' => now()]);
            foreach (['Honda', 'Suzuki', 'Yamaha', 'Kawasaki', 'Bajaj', 'TVS', 'Hero', 'KTM', 'Ducati', 'BMW', 'Harley-Davidson', 'Triumph', 'Royal Enfield', 'Benelli', 'CFMoto', 'Haojue', 'Vento', 'Freedom', 'Serpento', 'Italika'] as $name) {
                DB::table('vehicle_brands')->insertOrIgnore(['name' => $name, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
        });
    }

    public function down(): void
    {
        // Preserve catalog rows and vehicle references when rolling back.
        DB::table('vehicle_brands')->whereIn('name', ['Toyota', 'Nissan', 'Hyundai', 'Kia', 'Mitsubishi', 'Ford', 'Chevrolet', 'Mazda'])->update(['active' => true, 'updated_at' => now()]);
    }
};
