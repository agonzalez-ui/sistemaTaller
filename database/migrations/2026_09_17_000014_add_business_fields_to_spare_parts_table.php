<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {

            $table->string('code', 40)->unique();
            $table->string('name', 150);
            $table->string('description', 250)->nullable();
            $table->foreignId('spare_part_brand_id')->nullable()->constrained('spare_part_brands')->restrictOnDelete();
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_quantity')->default(0);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->index('name', 'ix_spare_parts_name');
        });
    }

    public function down(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            $table->dropForeign(['spare_part_brand_id']);
            $table->dropForeign(['created_by']);
            $table->dropUnique(['code']);
            $table->dropIndex('ix_spare_parts_name');
            $table->dropColumn([
                0 => 'code',
                1 => 'name',
                2 => 'description',
                3 => 'spare_part_brand_id',
                4 => 'price',
                5 => 'stock_quantity',
                6 => 'minimum_quantity',
                7 => 'active',
                8 => 'created_by',
            ]);
        });
    }
};
