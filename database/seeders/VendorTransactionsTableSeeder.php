<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VendorTransaction;
use App\Models\Order; // Import Order model for validation

class VendorTransactionsTableSeeder extends Seeder
{
    public function run()
    {
        $orders = Order::pluck('id'); // Get all order IDs
        $vendorTransactions = [
            [
                'vendor_id' => 2, // Vendor 1
                'order_id' => $orders->random(), // Random valid order_id
                'amount' => 200.00,
                'transaction_type' => 'sale',
            ],
            [
                'vendor_id' => 2, // Vendor 1
                'order_id' => $orders->random(), // Random valid order_id
                'amount' => -50.00, // Refund
                'transaction_type' => 'refund',
            ],
            [
                'vendor_id' => 3, // Vendor 2
                'order_id' => $orders->random(), // Random valid order_id
                'amount' => 120.50,
                'transaction_type' => 'sale',
            ],
        ];

        foreach ($vendorTransactions as $transaction) {
            VendorTransaction::create($transaction);
        }
    }
}
