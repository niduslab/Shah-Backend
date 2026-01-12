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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('return_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->enum('refund_type', ['full', 'partial']);
            $table->enum('refund_method', ['original_payment', 'store_credit', 'bank_transfer'])->default('original_payment');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('return_id')->references('id')->on('returns')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
