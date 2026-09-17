<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            $table->string('license_plate', 15)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('vehicle_brand_id')->constrained('vehicle_brands')->restrictOnDelete();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types')->restrictOnDelete();
            $table->string('model', 100);
            $table->unsignedSmallInteger('year');
            $table->string('color', 40)->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['vehicle_brand_id']);
            $table->dropForeign(['vehicle_type_id']);
            $table->dropForeign(['created_by']);
            $table->dropUnique(['license_plate']);
            $table->dropColumn([
                0 => 'license_plate',
                1 => 'customer_id',
                2 => 'vehicle_brand_id',
                3 => 'vehicle_type_id',
                4 => 'model',
                5 => 'year',
                6 => 'color',
                7 => 'active',
                8 => 'created_by',
            ]);
        });
    }
};
