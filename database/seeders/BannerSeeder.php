<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            // Homepage Hero Banners
            [
                'title' => 'Cricket Season Sale',
                'subtitle' => 'Up to 30% off on premium cricket equipment',
                'image' => 'banners/cricket-season-hero.jpg',
                'link' => '/category/cricket',
                'button_text' => 'Shop Now',
                'position' => 'homepage_hero',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'New Football Collection',
                'subtitle' => 'Latest boots and jerseys from Nike & Adidas',
                'image' => 'banners/football-collection-hero.jpg',
                'link' => '/category/football',
                'button_text' => 'Explore',
                'position' => 'homepage_hero',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Fitness Goals 2024',
                'subtitle' => 'Everything you need for your home gym',
                'image' => 'banners/fitness-hero.jpg',
                'link' => '/category/fitness',
                'button_text' => 'Get Started',
                'position' => 'homepage_hero',
                'sort_order' => 3,
                'is_active' => true,
            ],

            // Homepage Secondary Banners
            [
                'title' => 'Badminton Rackets',
                'subtitle' => 'Professional rackets from Yonex',
                'image' => 'banners/badminton-secondary.jpg',
                'link' => '/category/badminton-rackets',
                'button_text' => 'View Collection',
                'position' => 'homepage_secondary',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Swimming Gear',
                'subtitle' => 'Speedo & Arena swimwear',
                'image' => 'banners/swimming-secondary.jpg',
                'link' => '/category/swimming',
                'button_text' => 'Shop Swimwear',
                'position' => 'homepage_secondary',
                'sort_order' => 2,
                'is_active' => true,
            ],

            // Category Page Banners
            [
                'title' => 'Premium Cricket Bats',
                'subtitle' => 'English Willow from top brands',
                'image' => 'banners/cricket-bats-category.jpg',
                'link' => '/category/cricket-bats',
                'button_text' => 'Shop Bats',
                'position' => 'category_page',
                'sort_order' => 1,
                'is_active' => true,
            ],

            // Sidebar Banners
            [
                'title' => 'Free Shipping',
                'subtitle' => 'On orders above ৳5000',
                'image' => 'banners/free-shipping-sidebar.jpg',
                'link' => '/shipping-info',
                'button_text' => 'Learn More',
                'position' => 'sidebar',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'New Arrivals',
                'subtitle' => 'Check out latest products',
                'image' => 'banners/new-arrivals-sidebar.jpg',
                'link' => '/new-arrivals',
                'button_text' => 'View All',
                'position' => 'sidebar',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
