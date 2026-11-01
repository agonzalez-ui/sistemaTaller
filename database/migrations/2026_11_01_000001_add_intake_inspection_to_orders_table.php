<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('odometer')->nullable()->after('diagnosis');
            $table->string('fuel_level', 20)->nullable()->after('odometer');
            $table->text('intake_condition')->nullable()->after('fuel_level');
            $table->json('intake_accessories')->nullable()->after('intake_condition');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['odometer', 'fuel_level', 'intake_condition', 'intake_accessories']);
        });
    }
};
