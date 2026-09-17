<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            $table->string('identification_number', 20)->unique();
            $table->string('name', 150);
            $table->string('email', 150)->nullable();
            $table->string('address', 250)->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->index('name', 'ix_customers_name');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropUnique(['identification_number']);
            $table->dropIndex('ix_customers_name');
            $table->dropColumn([
                0 => 'identification_number',
                1 => 'name',
                2 => 'email',
                3 => 'address',
                4 => 'active',
                5 => 'created_by',
            ]);
        });
    }
};
