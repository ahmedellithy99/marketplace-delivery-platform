<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix: Make order lat/lng nullable (they're optional now)
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();
        });

        // Missing index: stores.store_type_id (filter by type)
        Schema::table('stores', function (Blueprint $table) {
            $table->index('store_type_id');
        });

        // Composite index: orders (user_id, status) for customer order listing
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'idx_orders_user_status_date');
        });

        // Composite index: deliveries for month filtering + active count
        Schema::table('deliveries', function (Blueprint $table) {
            $table->index(['delivery_man_id', 'delivered_at'], 'idx_deliveries_man_delivered');
            $table->index('assigned_at');
        });

        // Composite index: discounts for active lookup
        Schema::table('discounts', function (Blueprint $table) {
            $table->index(['is_active', 'starts_at', 'ends_at'], 'idx_discounts_active_dates');
        });

        // Composite index: notifications for unread listing
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_notifications_user_unread');
        });

        // Composite index: products for public listing
        Schema::table('products', function (Blueprint $table) {
            $table->index(['store_id', 'is_available', 'created_at'], 'idx_products_store_available');
            $table->index(['is_available', 'created_at'], 'idx_products_available_date');
            $table->index(['category_id', 'is_available'], 'idx_products_category_available');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex(['store_type_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_status_date');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex('idx_deliveries_man_delivered');
            $table->dropIndex(['assigned_at']);
        });

        Schema::table('discounts', function (Blueprint $table) {
            $table->dropIndex('idx_discounts_active_dates');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_unread');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_store_available');
            $table->dropIndex('idx_products_available_date');
            $table->dropIndex('idx_products_category_available');
        });
    }
};
