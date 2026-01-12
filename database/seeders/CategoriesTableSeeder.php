<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Parent categories
            ['category_name' => 'Electronics', 'icon' => 'electronics_icon.png', 'parent_category_id' => null],
            ['category_name' => 'Computers & Laptops', 'icon' => 'computers_icon.png', 'parent_category_id' => null],
            ['category_name' => 'Gaming', 'icon' => 'gaming_icon.png', 'parent_category_id' => null],
            ['category_name' => 'Mobile Phones', 'icon' => 'mobiles_icon.png', 'parent_category_id' => null],
            ['category_name' => 'Drones', 'icon' => 'drones_icon.png', 'parent_category_id' => null],
            ['category_name' => 'Accessories', 'icon' => 'accessories_icon.png', 'parent_category_id' => null],

            // Subcategories for Electronics
            ['category_name' => 'Smartphones', 'icon' => 'smartphones_icon.png', 'parent_category_id' => 4],
            ['category_name' => 'Smart Watches', 'icon' => 'smart_watches_icon.png', 'parent_category_id' => 1], // New Smart Watch category under Electronics
            ['category_name' => 'Chargers', 'icon' => 'chargers_icon.png', 'parent_category_id' => 6],
            ['category_name' => 'Gaming Consoles', 'icon' => 'gaming_consoles_icon.png', 'parent_category_id' => 3],

            // Subcategories for Computers & Laptops
            ['category_name' => 'Laptops', 'icon' => 'laptops_icon.png', 'parent_category_id' => 2],
            ['category_name' => 'Gaming Laptops', 'icon' => 'gaming_laptops_icon.png', 'parent_category_id' => 2],

            // Subcategories for Drones
            ['category_name' => 'Professional Drones', 'icon' => 'pro_drones_icon.png', 'parent_category_id' => 5],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
