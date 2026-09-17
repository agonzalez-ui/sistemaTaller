<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_status_id')->constrained('order_statuses')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('date');
            $table->string('notes', 250)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'date'], 'ix_hist_orden_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};
