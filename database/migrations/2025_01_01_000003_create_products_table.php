<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['simple', 'variant', 'measured'])->default('simple');
            $table->decimal('base_price', 10, 2)->nullable();
            $table->string('measurement_unit')->nullable(); // kg, g, liter, piece
            $table->decimal('min_quantity', 8, 3)->nullable();
            $table->decimal('max_quantity', 8, 3)->nullable();
            $table->decimal('quantity_step', 8, 3)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('store_id');
            $table->index('category_id');
            $table->index('type');
            $table->index(['store_id', 'is_available', 'created_at'], 'idx_products_store_available');
            $table->index(['is_available', 'created_at'], 'idx_products_available_date');
            $table->index(['category_id', 'is_available'], 'idx_products_category_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
