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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('discount_type', ['percent', 'flat'])->default('flat')->after('discount_amount');
            $table->string('shipping_address_line1')->nullable()->after('shipping_method');
            $table->string('shipping_address_line2')->nullable()->after('shipping_address_line1');
            $table->string('shipping_city')->nullable()->after('shipping_address_line2');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_zip_code')->nullable()->after('shipping_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'shipping_address_line1',
                'shipping_address_line2',
                'shipping_city',
                'shipping_state',
                'shipping_zip_code',
            ]);
        });
    }
};
