<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->restrictOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['module_id', 'role_id'], 'uq_module_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_role');
    }
};
