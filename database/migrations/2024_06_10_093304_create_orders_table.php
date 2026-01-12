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
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_number', 50)->unique();
            $table->enum('order_type', ['online', 'in_store'])->default('online');
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->unsignedBigInteger('billing_address_id')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->enum('shipping_method', ['shah_sports_team', 'pathao_courier'])->nullable();
            $table->string('tracking_number')->nullable();
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'return_requested',
                'return_approved',
                'refunded'
            ])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('shipping_address_id')->references('id')->on('addresses')->onDelete('set null');
            $table->foreign('billing_address_id')->references('id')->on('addresses')->onDelete('set null');
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
