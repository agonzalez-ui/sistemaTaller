<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->string('number', 20)->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('order_status_id')->constrained('order_statuses')->restrictOnDelete();
            $table->foreignId('mechanic_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('description', 500);
            $table->string('diagnosis', 500)->nullable();
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->dateTime('received_at');
            $table->dateTime('delivered_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->index(['order_status_id', 'received_at'], 'ix_ord_status_date');
            $table->index('received_at', 'ix_ord_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['order_status_id']);
            $table->dropForeign(['mechanic_id']);
            $table->dropForeign(['created_by']);
            $table->dropUnique(['number']);
            $table->dropIndex('ix_ord_status_date');
            $table->dropIndex('ix_ord_date');
            $table->dropColumn([
                0 => 'number',
                1 => 'customer_id',
                2 => 'vehicle_id',
                3 => 'order_status_id',
                4 => 'mechanic_id',
                5 => 'description',
                6 => 'diagnosis',
                7 => 'labor_cost',
                8 => 'received_at',
                9 => 'delivered_at',
                10 => 'created_by',
            ]);
        });
    }
};
