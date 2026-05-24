<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('order_number', 50)->unique();
            $table->enum('status', ['pending', 'accepted', 'preparing', 'on_the_way', 'delivered', 'cancelled'])->default('pending');
            $table->text('delivery_address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee_min', 10, 2);
            $table->decimal('delivery_fee_max', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status', 'created_at'], 'idx_orders_user_status_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
