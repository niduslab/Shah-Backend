<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Cricket
            [
                'name' => 'Cricket',
                'slug' => 'cricket',
                'description' => 'Professional cricket equipment and gear',
                'meta_title' => 'Cricket Equipment | Shah Sports',
                'meta_description' => 'Shop premium cricket bats, balls, pads, gloves and accessories',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Cricket Bats', 'slug' => 'cricket-bats', 'description' => 'English willow and Kashmir willow bats'],
                    ['name' => 'Cricket Balls', 'slug' => 'cricket-balls', 'description' => 'Leather and tennis balls'],
                    ['name' => 'Batting Pads', 'slug' => 'batting-pads', 'description' => 'Protective batting pads'],
                    ['name' => 'Batting Gloves', 'slug' => 'batting-gloves', 'description' => 'Professional batting gloves'],
                    ['name' => 'Wicket Keeping', 'slug' => 'wicket-keeping', 'description' => 'Wicket keeping gloves and pads'],
                    ['name' => 'Cricket Helmets', 'slug' => 'cricket-helmets', 'description' => 'Safety helmets for batsmen'],
                    ['name' => 'Cricket Bags', 'slug' => 'cricket-bags', 'description' => 'Kit bags and duffle bags'],
                    ['name' => 'Cricket Shoes', 'slug' => 'cricket-shoes', 'description' => 'Spikes and rubber sole shoes'],
                ],
            ],
            // Football
            [
                'name' => 'Football',
                'slug' => 'football',
                'description' => 'Football equipment and accessories',
                'meta_title' => 'Football Equipment | Shah Sports',
                'meta_description' => 'Shop footballs, boots, jerseys and training gear',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Footballs', 'slug' => 'footballs', 'description' => 'Match and training footballs'],
                    ['name' => 'Football Boots', 'slug' => 'football-boots', 'description' => 'Studs and turf shoes'],
                    ['name' => 'Football Jerseys', 'slug' => 'football-jerseys', 'description' => 'Club and national team jerseys'],
                    ['name' => 'Shin Guards', 'slug' => 'shin-guards', 'description' => 'Protective shin guards'],
                    ['name' => 'Goalkeeper Gear', 'slug' => 'goalkeeper-gear', 'description' => 'Gloves and protective gear'],
                    ['name' => 'Football Accessories', 'slug' => 'football-accessories', 'description' => 'Pumps, cones, and training equipment'],
                ],
            ],
            // Badminton
            [
                'name' => 'Badminton',
                'slug' => 'badminton',
                'description' => 'Badminton rackets and equipment',
                'meta_title' => 'Badminton Equipment | Shah Sports',
                'meta_description' => 'Shop badminton rackets, shuttlecocks and accessories',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Badminton Rackets', 'slug' => 'badminton-rackets', 'description' => 'Professional and recreational rackets'],
                    ['name' => 'Shuttlecocks', 'slug' => 'shuttlecocks', 'description' => 'Feather and nylon shuttlecocks'],
                    ['name' => 'Badminton Shoes', 'slug' => 'badminton-shoes', 'description' => 'Court shoes for badminton'],
                    ['name' => 'Badminton Bags', 'slug' => 'badminton-bags', 'description' => 'Racket bags and kit bags'],
                    ['name' => 'Badminton Nets', 'slug' => 'badminton-nets', 'description' => 'Portable and fixed nets'],
                ],
            ],
            // Tennis
            [
                'name' => 'Tennis',
                'slug' => 'tennis',
                'description' => 'Tennis rackets and equipment',
                'meta_title' => 'Tennis Equipment | Shah Sports',
                'meta_description' => 'Shop tennis rackets, balls and accessories',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Tennis Rackets', 'slug' => 'tennis-rackets', 'description' => 'Professional tennis rackets'],
                    ['name' => 'Tennis Balls', 'slug' => 'tennis-balls', 'description' => 'Match and practice balls'],
                    ['name' => 'Tennis Shoes', 'slug' => 'tennis-shoes', 'description' => 'Court shoes for tennis'],
                    ['name' => 'Tennis Bags', 'slug' => 'tennis-bags', 'description' => 'Racket bags'],
                ],
            ],
            // Fitness
            [
                'name' => 'Fitness',
                'slug' => 'fitness',
                'description' => 'Gym and fitness equipment',
                'meta_title' => 'Fitness Equipment | Shah Sports',
                'meta_description' => 'Shop dumbbells, weights, yoga mats and fitness accessories',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Dumbbells & Weights', 'slug' => 'dumbbells-weights', 'description' => 'Free weights and dumbbells'],
                    ['name' => 'Yoga & Pilates', 'slug' => 'yoga-pilates', 'description' => 'Yoga mats and accessories'],
                    ['name' => 'Resistance Bands', 'slug' => 'resistance-bands', 'description' => 'Exercise bands'],
                    ['name' => 'Skipping Ropes', 'slug' => 'skipping-ropes', 'description' => 'Jump ropes for cardio'],
                    ['name' => 'Exercise Machines', 'slug' => 'exercise-machines', 'description' => 'Home gym equipment'],
                ],
            ],
            // Swimming
            [
                'name' => 'Swimming',
                'slug' => 'swimming',
                'description' => 'Swimming gear and accessories',
                'meta_title' => 'Swimming Equipment | Shah Sports',
                'meta_description' => 'Shop swimwear, goggles and swimming accessories',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Swimwear', 'slug' => 'swimwear', 'description' => 'Swimming costumes and trunks'],
                    ['name' => 'Swimming Goggles', 'slug' => 'swimming-goggles', 'description' => 'Goggles for swimming'],
                    ['name' => 'Swim Caps', 'slug' => 'swim-caps', 'description' => 'Silicone and latex caps'],
                    ['name' => 'Swimming Accessories', 'slug' => 'swimming-accessories', 'description' => 'Kickboards, fins, and more'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);
            
            $parent = Category::create(array_merge($categoryData, ['is_active' => true]));
            
            foreach ($children as $index => $child) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $child['name'],
                    'slug' => $child['slug'],
                    'description' => $child['description'],
                    'meta_title' => $child['name'] . ' | Shah Sports',
                    'meta_description' => 'Shop ' . strtolower($child['name']) . ' at Shah Sports',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
