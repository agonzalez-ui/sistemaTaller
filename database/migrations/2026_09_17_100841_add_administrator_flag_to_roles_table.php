<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_administrator')->default(false);
        });
        DB::table('roles')->where('id', 1)->where('name', 'Administrador')->update(['is_administrator' => true]);
        DB::table('modules')->where('slug', 'ordenes')->update(['slug' => 'orders']);
    }

    public function down(): void
    {
        DB::table('modules')->where('slug', 'orders')->update(['slug' => 'ordenes']);
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_administrator');
        });
    }
};
