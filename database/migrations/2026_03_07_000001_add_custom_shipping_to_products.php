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
        Schema::table('products', function (Blueprint $table) {
            // Custom shipping configuration
            $table->enum('shipping_type', ['default', 'free', 'fixed', 'per_item'])->default('default')->after('height');
            $table->decimal('shipping_cost', 10, 2)->nullable()->after('shipping_type');
            $table->boolean('requires_shipping')->default(true)->after('shipping_cost');
            
            // Additional shipping options
            $table->boolean('separate_shipping')->default(false)->after('requires_shipping');
            $table->text('shipping_notes')->nullable()->after('separate_shipping');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            // Allow variations to override product shipping
            $table->enum('shipping_type', ['inherit', 'free', 'fixed', 'per_item'])->default('inherit')->after('quantity');
            $table->decimal('shipping_cost', 10, 2)->nullable()->after('shipping_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_type',
                'shipping_cost',
                'requires_shipping',
                'separate_shipping',
                'shipping_notes',
            ]);
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_type',
                'shipping_cost',
            ]);
        });
    }
};
