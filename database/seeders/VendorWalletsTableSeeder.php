<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VendorWallet;

class VendorWalletsTableSeeder extends Seeder
{
    public function run()
    {
        $vendorWallets = [
            ['vendor_id' => 1, 'balance' => 5000.00], // Admin's shop
            ['vendor_id' => 2, 'balance' => 1200.50], // Vendor 1
            ['vendor_id' => 3, 'balance' => 800.75],  // Vendor 2
        ];

        foreach ($vendorWallets as $wallet) {
            VendorWallet::create($wallet);
        }
    }
}
