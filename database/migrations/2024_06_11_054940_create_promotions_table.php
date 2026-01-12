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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('promotion_type', [
                'percentage',
                'fixed_amount',
                'flash_sale',
                'combo_offer',
                'free_delivery'
            ]);
            $table->decimal('discount_value', 10, 2);
            $table->enum('applies_to', [
                'all_products',
                'specific_products',
                'specific_brands',
                'specific_categories'
            ])->default('all_products');
            $table->enum('apply_level', ['product', 'cart'])->default('product');
            $table->decimal('min_purchase_amount', 10, 2)->default(0);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
