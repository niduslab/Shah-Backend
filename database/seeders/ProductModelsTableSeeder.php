<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductModel;

class ProductModelsTableSeeder extends Seeder
{
    public function run()
    {
        $models = [
            'Samsung Galaxy Z Fold 5',
            'iPhone 15 Pro',
            'Apple iPhone 14 Pro Max 256GB Deep Purple',
            'Apple MacBook Pro 16 inch M3 Max Chip',
            'MSI Raider 18 HX A14VIG Core i9 14th Gen',
            'Asus ROG Strix SCAR 18 G834JYR Intel Core i9',
            'Hoco N40 Mighty Single Port PD20W Charger',
            'Hoco CW6 Pro Easy 15W Wireless Fast Charger',
            'EarFun EH100 in-Ear Audiophile Earphone',
            'Sony PlayStation PULSE Explore Wireless Earbuds',
            'Apple AirPods Pro 2nd Generation Wireless Earbuds',
            'Sony PlayStation 5 Digital Edition Slim Gaming Console',
            'Nintendo Switch – OLED Model w/ Neon Red & Neon Blue Joy-Con',
            'DJI Mavic 3 Pro Cine with DJI RC Pro',
        ];

        foreach ($models as $model) {
            ProductModel::create([
                'model_name' => $model,
            ]);
        }
    }
}
