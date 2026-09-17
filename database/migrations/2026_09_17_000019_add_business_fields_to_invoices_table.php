<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->string('number', 20)->unique();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->dateTime('date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(13);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status', 15)->default('ISSUED');
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancellation_reason', 250)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->index(['date', 'status'], 'ix_fact_date_status');
            $table->index('customer_id', 'ix_fact_customer');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['created_by']);
            $table->dropUnique(['number']);
            $table->dropIndex('ix_fact_date_status');
            $table->dropIndex('ix_fact_customer');
            $table->dropColumn([
                0 => 'number',
                1 => 'order_id',
                2 => 'customer_id',
                3 => 'vehicle_id',
                4 => 'date',
                5 => 'subtotal',
                6 => 'discount',
                7 => 'tax_rate',
                8 => 'tax_amount',
                9 => 'total',
                10 => 'status',
                11 => 'cancelled_at',
                12 => 'cancellation_reason',
                13 => 'created_by',
            ]);
        });
    }
};
