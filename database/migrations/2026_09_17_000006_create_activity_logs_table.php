<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('occurred_at');
            $table->string('action', 20);
            $table->string('table_name', 100);
            $table->string('record_id', 100)->nullable();
            $table->string('details', 500);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'occurred_at'], 'ix_mov_user_date');
            $table->index('action', 'ix_mov_type');
            $table->index('occurred_at', 'ix_mov_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
