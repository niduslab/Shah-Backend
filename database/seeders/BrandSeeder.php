<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing brands (disable foreign key checks to avoid constraint errors)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Brand::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $brands = [
            // Fitness Equipment Brands
            ['name' => 'Adidas', 'slug' => 'adidas', 'image_file' => 'adidas.png', 'description' => 'German multinational sports brand', 'sort_order' => 1],
            ['name' => 'Reebok', 'slug' => 'reebok', 'image_file' => 'reebok.png', 'description' => 'Fitness and lifestyle brand', 'sort_order' => 2],
            ['name' => 'Shua', 'slug' => 'shua', 'image_file' => 'shua.png', 'description' => 'Commercial fitness equipment brand', 'sort_order' => 3],
            ['name' => 'Body Solid', 'slug' => 'body-solid', 'image_file' => 'bodysolid.png', 'description' => 'American fitness equipment manufacturer', 'sort_order' => 4],
            ['name' => 'Marcy', 'slug' => 'marcy', 'image_file' => 'marcy.png', 'description' => 'Home and commercial fitness equipment', 'sort_order' => 5],
            ['name' => 'NordicTrack', 'slug' => 'nordictrack', 'image_file' => 'nordictrack.png', 'description' => 'Premium fitness equipment brand', 'sort_order' => 6],
            ['name' => 'ProForm', 'slug' => 'proform', 'image_file' => 'pro-form.png', 'description' => 'Home fitness equipment brand', 'sort_order' => 7],
            ['name' => 'FreeMotion', 'slug' => 'freemotion', 'image_file' => 'freemotion.png', 'description' => 'Commercial fitness equipment', 'sort_order' => 8],
            ['name' => 'Spirit', 'slug' => 'spirit', 'image_file' => 'spirit.png', 'description' => 'Quality fitness equipment manufacturer', 'sort_order' => 9],
            ['name' => 'Sole', 'slug' => 'sole', 'image_file' => 'sole.png', 'description' => 'Premium home fitness equipment', 'sort_order' => 10],
            ['name' => 'Xterra', 'slug' => 'xterra', 'image_file' => 'xterra.png', 'description' => 'Affordable fitness equipment brand', 'sort_order' => 11],
            ['name' => 'SteelFlex', 'slug' => 'steelflex', 'image_file' => 'steelflex.png', 'description' => 'Strength training equipment', 'sort_order' => 12],
            ['name' => 'iRest', 'slug' => 'irest', 'image_file' => 'irest.png', 'description' => 'Massage chair and wellness brand', 'sort_order' => 13],
            ['name' => 'MD Buddy', 'slug' => 'md-buddy', 'image_file' => 'mdbuddy.png', 'description' => 'Fitness accessories and equipment', 'sort_order' => 14],
            ['name' => 'UFC', 'slug' => 'ufc', 'image_file' => 'ufc.png', 'description' => 'MMA and combat sports equipment', 'sort_order' => 15],
            ['name' => 'Wave', 'slug' => 'wave', 'image_file' => 'wave.png', 'description' => 'Fitness and sports equipment', 'sort_order' => 16],
            ['name' => 'Rider', 'slug' => 'rider', 'image_file' => 'rider.png', 'description' => 'Exercise bikes and fitness equipment', 'sort_order' => 17],
            ['name' => 'Atlas', 'slug' => 'atlas', 'image_file' => 'altas.png', 'description' => 'Strength training equipment', 'sort_order' => 18],
            ['name' => 'APC', 'slug' => 'apc', 'image_file' => 'apc.png', 'description' => 'Fitness equipment manufacturer', 'sort_order' => 19],
            ['name' => 'Boker', 'slug' => 'boker', 'image_file' => 'boker.png', 'description' => 'Sports and fitness equipment', 'sort_order' => 20],
            ['name' => 'Stex', 'slug' => 'stex', 'image_file' => 'stex.png', 'description' => 'Fitness equipment brand', 'sort_order' => 21],
            ['name' => 'XPD', 'slug' => 'xpd', 'image_file' => 'xpd.png', 'description' => 'Sports equipment manufacturer', 'sort_order' => 22],
            ['name' => 'ZXCN', 'slug' => 'zxcn', 'image_file' => 'zxcn.png', 'description' => 'Fitness equipment brand', 'sort_order' => 23],
            ['name' => '729', 'slug' => '729', 'image_file' => '729.png', 'description' => 'Table tennis equipment brand', 'sort_order' => 24],
        ];

        $sourceDir = public_path('images/all-brands');
        $storageDir = storage_path('app/public/brands');

        // Ensure storage directory exists
        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        foreach ($brands as $brandData) {
            $logoPath = null;

            // Copy image from public/images/all-brands to storage/app/public/brands
            if (isset($brandData['image_file'])) {
                $sourceFile = $sourceDir . '/' . $brandData['image_file'];
                
                if (File::exists($sourceFile)) {
                    $destinationFile = $storageDir . '/' . $brandData['image_file'];
                    
                    // Copy file to storage
                    File::copy($sourceFile, $destinationFile);
                    
                    // Store path relative to storage/app/public (as expected by Storage::disk('public'))
                    $logoPath = 'brands/' . $brandData['image_file'];
                }
            }

            // Create brand
            Brand::create([
                'name' => $brandData['name'],
                'slug' => $brandData['slug'],
                'logo' => $logoPath,
                'description' => $brandData['description'],
                'sort_order' => $brandData['sort_order'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Brands seeded successfully with images copied to storage!');
    }
}
