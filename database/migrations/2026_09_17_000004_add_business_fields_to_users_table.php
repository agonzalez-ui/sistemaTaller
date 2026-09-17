<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('identification_number', 20)->nullable()->unique();
            $table->string('username', 60)->nullable()->unique();
            $table->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete();
            $table->date('joined_at')->nullable();
            $table->boolean('active')->default(true);
            $table->index('active', 'ix_users_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropUnique(['identification_number']);
            $table->dropUnique(['username']);
            $table->dropIndex('ix_users_active');
            $table->dropColumn([
                0 => 'identification_number',
                1 => 'username',
                2 => 'role_id',
                3 => 'joined_at',
                4 => 'active',
            ]);
        });
    }
};
