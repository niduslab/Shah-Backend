<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
use Illuminate\Support\Str;

class DownloadableProductSeeder extends Seeder
{
    public function run()
    {
        // Get product IDs
        $productIds = DB::table('products')->pluck('id');

        // Generate sample downloadable products
        for ($i = 1; $i <= 30; $i++) {
            DB::table('downloadable_products')->insert([
                'product_id' => $productIds->random(),
                'file_name' => 'File_' . $i . '.pdf', // Example file name
                'file_path' => 'files/downloadable/product_' . $i . '.pdf', // Example file path
                'description' => 'This is a description for downloadable product ' . $i,
                'secret_key' => Str::random(16), // Optional secret key for access
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
