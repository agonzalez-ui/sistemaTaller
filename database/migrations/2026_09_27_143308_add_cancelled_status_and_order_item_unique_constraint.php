<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('order_statuses')->exists()) {
            DB::table('order_statuses')->where('name', 'En diagnostico')->update(['name' => 'En diagnóstico', 'updated_at' => now()]);
            DB::table('order_statuses')->where('name', 'En reparacion')->update(['name' => 'En reparación', 'updated_at' => now()]);
            DB::table('order_statuses')->insertOrIgnore([
                'name' => 'Cancelada', 'color_class' => 'bg-red-100 text-red-800',
                'sort_order' => 70, 'is_final' => true, 'allows_invoicing' => false,
                'active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        Schema::table('order_items', function (Blueprint $table) {
            $table->unique(['order_id', 'spare_part_id'], 'uq_order_items_order_part');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('uq_order_items_order_part');
        });
        DB::table('order_statuses')->where('name', 'Cancelada')->delete();
        DB::table('order_statuses')->where('name', 'En diagnóstico')->update(['name' => 'En diagnostico', 'updated_at' => now()]);
        DB::table('order_statuses')->where('name', 'En reparación')->update(['name' => 'En reparacion', 'updated_at' => now()]);
    }
};
