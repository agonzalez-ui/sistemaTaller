<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->restrictOnDelete();
            $table->string('type', 15);
            $table->integer('quantity');
            $table->integer('previous_balance');
            $table->integer('new_balance');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('date');
            $table->string('notes', 250)->nullable();
            $table->timestamps();

            $table->index(['spare_part_id', 'date'], 'ix_movinv_part_date');
            $table->index('type', 'ix_movinv_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
