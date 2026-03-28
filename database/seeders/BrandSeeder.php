<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            // Cricket brands
            ['name' => 'Gray-Nicolls', 'slug' => 'gray-nicolls', 'description' => 'Premium English cricket equipment', 'sort_order' => 1],
            ['name' => 'Kookaburra', 'slug' => 'kookaburra', 'description' => 'Australian cricket brand', 'sort_order' => 2],
            ['name' => 'SG', 'slug' => 'sg', 'description' => 'Sanspareils Greenlands - Indian cricket brand', 'sort_order' => 3],
            ['name' => 'SS', 'slug' => 'ss', 'description' => 'Sareen Sports - Premium cricket equipment', 'sort_order' => 4],
            ['name' => 'MRF', 'slug' => 'mrf', 'description' => 'MRF Cricket Equipment', 'sort_order' => 5],
            ['name' => 'GM', 'slug' => 'gm', 'description' => 'Gunn & Moore - English cricket brand', 'sort_order' => 6],
            ['name' => 'DSC', 'slug' => 'dsc', 'description' => 'DSC Cricket Equipment', 'sort_order' => 7],
            
            // Football brands
            ['name' => 'Nike', 'slug' => 'nike', 'description' => 'Global sports brand', 'sort_order' => 8],
            ['name' => 'Adidas', 'slug' => 'adidas', 'description' => 'German sports brand', 'sort_order' => 9],
            ['name' => 'Puma', 'slug' => 'puma', 'description' => 'German sports brand', 'sort_order' => 10],
            ['name' => 'Under Armour', 'slug' => 'under-armour', 'description' => 'American sports brand', 'sort_order' => 11],
            
            // Badminton/Tennis brands
            ['name' => 'Yonex', 'slug' => 'yonex', 'description' => 'Japanese racket sports brand', 'sort_order' => 12],
            ['name' => 'Li-Ning', 'slug' => 'li-ning', 'description' => 'Chinese sports brand', 'sort_order' => 13],
            ['name' => 'Victor', 'slug' => 'victor', 'description' => 'Taiwanese badminton brand', 'sort_order' => 14],
            ['name' => 'Wilson', 'slug' => 'wilson', 'description' => 'American sports equipment brand', 'sort_order' => 15],
            ['name' => 'Head', 'slug' => 'head', 'description' => 'Austrian sports brand', 'sort_order' => 16],
            ['name' => 'Babolat', 'slug' => 'babolat', 'description' => 'French tennis brand', 'sort_order' => 17],
            
            // Fitness brands
            ['name' => 'Reebok', 'slug' => 'reebok', 'description' => 'Fitness and lifestyle brand', 'sort_order' => 18],
            ['name' => 'Decathlon', 'slug' => 'decathlon', 'description' => 'French sporting goods retailer', 'sort_order' => 19],
            ['name' => 'Domyos', 'slug' => 'domyos', 'description' => 'Decathlon fitness brand', 'sort_order' => 20],
            ['name' => 'Shua', 'slug' => 'shua', 'description' => 'Commercial fitness equipment brand', 'sort_order' => 21],
            ['name' => 'Body Solid', 'slug' => 'body-solid', 'description' => 'American fitness equipment manufacturer', 'sort_order' => 22],
            ['name' => 'Marcy', 'slug' => 'marcy', 'description' => 'Home and commercial fitness equipment', 'sort_order' => 23],
            
            // Swimming brands
            ['name' => 'Speedo', 'slug' => 'speedo', 'description' => 'Australian swimwear brand', 'sort_order' => 24],
            ['name' => 'Arena', 'slug' => 'arena', 'description' => 'Italian swimwear brand', 'sort_order' => 25],
            ['name' => 'TYR', 'slug' => 'tyr', 'description' => 'American swimwear brand', 'sort_order' => 26],
            
            // Other sports brands
            ['name' => 'Butterfly', 'slug' => 'butterfly', 'description' => 'Japanese table tennis brand', 'sort_order' => 27],
            ['name' => 'Spalding', 'slug' => 'spalding', 'description' => 'American basketball equipment brand', 'sort_order' => 28],
            ['name' => 'Everlast', 'slug' => 'everlast', 'description' => 'American boxing equipment brand', 'sort_order' => 29],
        ];

        foreach ($brands as $brand) {
            Brand::create(array_merge($brand, ['is_active' => true]));
        }
    }
}
