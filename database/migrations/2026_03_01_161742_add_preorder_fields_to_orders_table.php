<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_preorder')->default(false)->after('order_type');
            $table->decimal('preorder_deposit_paid', 10, 2)->nullable()->after('is_preorder');
            $table->decimal('preorder_remaining_amount', 10, 2)->nullable()->after('preorder_deposit_paid');
            $table->enum('preorder_payment_status', ['deposit_paid', 'fully_paid', 'pending'])->default('pending')->after('preorder_remaining_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'is_preorder',
                'preorder_deposit_paid',
                'preorder_remaining_amount',
                'preorder_payment_status'
            ]);
        });
    }
};
