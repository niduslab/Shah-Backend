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
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('product_id');
            $table->primary(['promotion_id', 'product_id']);

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('promotion_brands', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('brand_id');
            $table->primary(['promotion_id', 'brand_id']);

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
        });

        Schema::create('promotion_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('category_id');
            $table->primary(['promotion_id', 'category_id']);

            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_categories');
        Schema::dropIfExists('promotion_brands');
        Schema::dropIfExists('promotion_products');
    }
};
