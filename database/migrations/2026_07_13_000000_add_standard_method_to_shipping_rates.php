<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE shipping_rates MODIFY COLUMN method ENUM('shah_sports_team', 'pathao_courier', 'standard') NOT NULL");

        // Standard Shipping was previously hard-coded in ShippingService. Give it a
        // real rate row so its cost, delivery time and free-shipping threshold are
        // admin-managed like every other method. No free shipping until an admin
        // sets a threshold above 0.
        $exists = DB::table('shipping_rates')->where('method', 'standard')->exists();

        if (!$exists) {
            DB::table('shipping_rates')->insert([
                'name' => 'Standard Shipping',
                'shipping_class_id' => null,
                'method' => 'standard',
                'country' => 'BD',
                'delivery_time' => '3-5 business days',
                'free_shipping_min_order' => 0,
                'base_cost' => 60.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('shipping_rates')->where('method', 'standard')->delete();

        DB::statement("ALTER TABLE shipping_rates MODIFY COLUMN method ENUM('shah_sports_team', 'pathao_courier') NOT NULL");
    }
};
