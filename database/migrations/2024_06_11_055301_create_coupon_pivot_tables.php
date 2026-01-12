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
        Schema::create('coupon_products', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('product_id');
            $table->primary(['coupon_id', 'product_id']);

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('coupon_brands', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('brand_id');
            $table->primary(['coupon_id', 'brand_id']);

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
        });

        Schema::create('coupon_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('category_id');
            $table->primary(['coupon_id', 'category_id']);

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_categories');
        Schema::dropIfExists('coupon_brands');
        Schema::dropIfExists('coupon_products');
    }
};
