<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('discount_type', ['percentage', 'fixed_amount'])->default('percentage');
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->integer('quantity_limit')->nullable();
            $table->integer('quantity_sold')->default(0);
            $table->integer('per_user_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('flash_deal_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flash_deal_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('flash_price', 10, 2);
            $table->integer('quantity_limit')->nullable();
            $table->integer('quantity_sold')->default(0);
            $table->timestamps();

            $table->foreign('flash_deal_id')->references('id')->on('flash_deals')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['flash_deal_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_deal_products');
        Schema::dropIfExists('flash_deals');
    }
};
