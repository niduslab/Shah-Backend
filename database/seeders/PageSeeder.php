<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        // Landing Page
        $landingPage = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'type' => 'landing',
            'meta_title' => 'Fitness Equipment Store',
            'is_active' => true,
        ]);

        // Hero Section
        PageSection::create([
            'page_id' => $landingPage->id,
            'section_type' => 'hero_slider',
            'title' => 'Hero Slider',
            'content' => [
                'slides' => [
                    [
                        'type' => 'image',
                        'media_url' => '/images/hero-1.jpg',
                        'title' => 'Elevate Your Fitness Journey',
                        'subtitle' => 'Up to 40% Discounts',
                        'cta_text' => 'Shop Now',
                        'cta_link' => '/shop',
                    ],
                ],
            ],
            'settings' => [
                'autoplay' => true,
                'interval' => 5000,
                'show_arrows' => true,
            ],
            'sort_order' => 1,
        ]);

        // Product Grid Section
        PageSection::create([
            'page_id' => $landingPage->id,
            'section_type' => 'product_grid',
            'title' => 'Featured Products',
            'content' => [
                'layout' => 'grid',
                'columns' => 4,
                'items' => [
                    [
                        'type' => 'product_card',
                        'image' => '/images/gear.jpg',
                        'title' => 'Perfect Gear Awaits',
                        'cta_text' => 'Shop Now',
                        'cta_link' => '/category/gear',
                    ],
                    [
                        'type' => 'product_card',
                        'image' => '/images/weights.jpg',
                        'title' => 'Shine Bright with Weights',
                        'cta_text' => 'Shop Now',
                        'cta_link' => '/category/weights',
                    ],
                ],
            ],
            'sort_order' => 2,
        ]);

        // Brand Showcase Section
        PageSection::create([
            'page_id' => $landingPage->id,
            'section_type' => 'brand_showcase',
            'title' => 'Turn Your Home Into A Complete Fitness Space',
            'content' => [
                'background_type' => 'image',
                'background_url' => '/images/nordictrack.jpg',
                'description' => 'NordicTrack is a leader in home fitness equipment, known for innovative treadmills, ellipticals, and exercise bikes with iFIT technology integration.',
                'cta_text' => 'Shop Nordictrack',
                'cta_link' => '/brand/nordictrack',
            ],
            'settings' => [
                'text_color' => 'white',
                'overlay' => true,
            ],
            'sort_order' => 3,
        ]);

        // Category Grid Section
        PageSection::create([
            'page_id' => $landingPage->id,
            'section_type' => 'category_grid',
            'title' => 'Explore The Nordictrack Categories',
            'content' => [
                'categories' => [
                    ['name' => 'Bikes', 'image' => '/images/bikes.jpg', 'link' => '/category/bikes'],
                    ['name' => 'Treadmills', 'image' => '/images/treadmills.jpg', 'link' => '/category/treadmills'],
                    ['name' => 'Ellipticals', 'image' => '/images/ellipticals.jpg', 'link' => '/category/ellipticals'],
                    ['name' => 'Rowers', 'image' => '/images/rowers.jpg', 'link' => '/category/rowers'],
                ],
            ],
            'settings' => [
                'columns' => 4,
                'show_border' => true,
            ],
            'sort_order' => 4,
        ]);
    }
}
