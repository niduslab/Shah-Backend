<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class BrandsTableSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            ['brand_name' => 'Samsung', 'logo' => 'samsung_logo.png', 'description' => 'Leading technology company.', 'priority' => 'high'],
            ['brand_name' => 'Apple', 'logo' => 'apple_logo.png', 'description' => 'Innovative tech giant.', 'priority' => 'high'],
            ['brand_name' => 'MSI', 'logo' => 'msi_logo.png', 'description' => 'Gaming hardware specialist.', 'priority' => 'high'],
            ['brand_name' => 'Asus', 'logo' => 'asus_logo.png', 'description' => 'Expert in gaming and computing devices.', 'priority' => 'medium'],
            ['brand_name' => 'Hoco', 'logo' => 'hoco_logo.png', 'description' => 'Affordable and efficient accessories.', 'priority' => 'low'],
            ['brand_name' => 'EarFun', 'logo' => 'earfun_logo.png', 'description' => 'Audiophile-grade earphones.', 'priority' => 'low'],
            ['brand_name' => 'Sony', 'logo' => 'sony_logo.png', 'description' => 'Pioneer in electronics and gaming.', 'priority' => 'high'],
            ['brand_name' => 'Nintendo', 'logo' => 'nintendo_logo.png', 'description' => 'Iconic video game company.', 'priority' => 'high'],
            ['brand_name' => 'DJI', 'logo' => 'dji_logo.png', 'description' => 'Leader in drone technology.', 'priority' => 'high'],
        ];

        foreach ($brands as $brand) {
            // Path where logo should be stored in the storage/logos folder
            $logoPath = 'storage/logos/' . $brand['logo'];

            // If the logo file exists in public/assets/imgs/brands, move it to storage/logos
            if (Storage::exists('public/assets/imgs/brands/' . $brand['logo'])) {
                Storage::copy('public/assets/imgs/brands/' . $brand['logo'], $logoPath);
            }

            // Create brand record
            Brand::create([
                'brand_name' => $brand['brand_name'],
                'logo' => $logoPath, // Save the storage path of the logo
                'description' => $brand['description'],
                'priority' => $brand['priority'],
            ]);
        }
    }
}
