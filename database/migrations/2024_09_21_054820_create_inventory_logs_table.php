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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variation_id')->nullable();
            $table->integer('quantity_before');
            $table->integer('quantity_change');
            $table->integer('quantity_after');
            $table->enum('reason', ['sale', 'return', 'adjustment', 'restock', 'damage', 'pos_sale']);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
