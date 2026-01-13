<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class ProductModelSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            // Gray-Nicolls models
            'Gray-Nicolls' => ['Powerbow', 'Shockwave', 'Predator', 'Oblivion', 'Kronus'],
            // Kookaburra models
            'Kookaburra' => ['Kahuna', 'Ghost', 'Beast', 'Blaze', 'Rapid'],
            // SG models
            'SG' => ['Sunny Tonny', 'Cobra', 'Sierra', 'Nexus', 'RSD Xtreme'],
            // SS models
            'SS' => ['Ton', 'Master', 'Gladiator', 'Ranger', 'Premium'],
            // Nike models
            'Nike' => ['Mercurial', 'Phantom', 'Tiempo', 'Air Max', 'Revolution'],
            // Adidas models
            'Adidas' => ['Predator', 'X Speedflow', 'Copa', 'Ultraboost', 'Duramo'],
            // Yonex models
            'Yonex' => ['Astrox', 'Nanoflare', 'Arcsaber', 'Voltric', 'Duora'],
            // Wilson models
            'Wilson' => ['Pro Staff', 'Blade', 'Clash', 'Ultra', 'Burn'],
            // Speedo models
            'Speedo' => ['Fastskin', 'Endurance', 'Aquapulse', 'Biofuse', 'Futura'],
        ];

        foreach ($models as $brandName => $modelNames) {
            $brand = Brand::where('name', $brandName)->first();
            if ($brand) {
                foreach ($modelNames as $modelName) {
                    ProductModel::create([
                        'brand_id' => $brand->id,
                        'name' => $modelName,
                    ]);
                }
            }
        }
    }
}
