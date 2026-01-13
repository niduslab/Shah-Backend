<?php

namespace Database\Seeders;

use App\Models\ShippingClass;
use Illuminate\Database\Seeder;

class ShippingClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Standard shipping for regular items',
            ],
            [
                'name' => 'Heavy Items',
                'slug' => 'heavy-items',
                'description' => 'For items over 5kg like gym equipment',
            ],
            [
                'name' => 'Fragile',
                'slug' => 'fragile',
                'description' => 'For delicate items requiring careful handling',
            ],
            [
                'name' => 'Oversized',
                'slug' => 'oversized',
                'description' => 'For large items like cricket bags and equipment',
            ],
        ];

        foreach ($classes as $class) {
            ShippingClass::create($class);
        }
    }
}
