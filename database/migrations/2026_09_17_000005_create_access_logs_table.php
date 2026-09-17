<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('logged_in_at');
            $table->dateTime('logged_out_at')->nullable();
            $table->string('logout_type', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'logged_in_at'], 'ix_acc_user_date');
            $table->index('logged_in_at', 'ix_acc_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
