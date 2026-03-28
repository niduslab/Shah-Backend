<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks and clear existing categories
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /*
        |--------------------------------------------------------------------------
        | FITNESS
        |--------------------------------------------------------------------------
        */
        $fitness = $this->createCategory('Fitness', null, 1);

        // HOME
        $home = $this->createCategory('Home', $fitness->id, 1);
        $homeCardio = $this->createCategory('Cardio', $home->id, 1);
        $this->createCategory('Shua', $homeCardio->id, 1);

        // COMMERCIAL
        $commercial = $this->createCategory('Commercial', $fitness->id, 2);
        $commercialCardio = $this->createCategory('Cardio', $commercial->id, 1);
        
        $commercialBrands = ['Marcy', 'Body Solid'];
        foreach ($commercialBrands as $index => $brand) {
            $this->createCategory($brand, $commercialCardio->id, $index + 1);
        }

        // CARDIO (Direct under Fitness)
        $cardio = $this->createCategory('Cardio', $fitness->id, 3);
        $cardioItems = ['Bike', 'Treadmill', 'Elliptical', 'Rowing', 'Stair Climber'];
        foreach ($cardioItems as $index => $item) {
            $this->createCategory($item, $cardio->id, $index + 1);
        }

        // STRENGTH (Direct under Fitness)
        $strength = $this->createCategory('Strength', $fitness->id, 4);
        $strengthItems = [
            'Selectorized Series',
            'Plate Loaded Series',
            'Hammer Series',
            'Multi Station Gym',
            'Functional Trainer'
        ];
        foreach ($strengthItems as $index => $item) {
            $this->createCategory($item, $strength->id, $index + 1);
        }

        // FREE WEIGHT (Direct under Fitness)
        $freeWeight = $this->createCategory('Free Weight', $fitness->id, 5);
        $freeWeightItems = [
            'Barbell',
            'Dumbbell',
            'Bench',
            'Weight Plate',
            'Fitness Accessories'
        ];
        foreach ($freeWeightItems as $index => $item) {
            $this->createCategory($item, $freeWeight->id, $index + 1);
        }

        /*
        |--------------------------------------------------------------------------
        | SPORTS
        |--------------------------------------------------------------------------
        */
        $sports = $this->createCategory('Sports', null, 2);
        
        $sportsItems = [
            'Cricket',
            'Football',
            'Table Tennis',
            'Hockey',
            'Basketball',
            'Boxing',
            'Billiard',
            'Swimming',
        ];
        foreach ($sportsItems as $index => $item) {
            $this->createCategory($item, $sports->id, $index + 1);
        }
    }

    /**
     * Helper method to create a category with auto-generated slug and meta fields.
     */
    private function createCategory(string $name, ?int $parentId, int $sortOrder): Category
    {
        $slug = Str::slug($name);
        
        // Make slug unique if it already exists by appending parent info
        $existingCategory = Category::where('slug', $slug)->first();
        if ($existingCategory) {
            if ($parentId) {
                $parent = Category::find($parentId);
                $slug = Str::slug($parent->name . '-' . $name);
            } else {
                $slug = Str::slug($name . '-' . uniqid());
            }
        }
        
        return Category::create([
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId,
            'description' => $name . ' equipment and accessories',
            'meta_title' => $name . ' | Shah Sports',
            'meta_description' => 'Shop ' . strtolower($name) . ' equipment at Shah Sports',
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);
    }
}
