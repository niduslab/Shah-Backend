<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Seeder;

class PageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // LANDING PAGE (Generic Homepage)
        // ============================================
        $landingPage = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'type' => 'landing',
            'meta_title' => 'Premium Fitness Equipment - Transform Your Home Gym',
            'meta_description' => 'Discover premium fitness equipment for your home gym. Shop treadmills, bikes, ellipticals, and more.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // 1. Landing Hero Grid (4 sections)
        PageSection::create([
            'page_id' => $landingPage->id,
            'section_type' => 'landing_hero_grid',
            'title' => 'Hero Grid',
            'content' => [
                'use_video' => false,
                
                // Main card (left - large with bike)
                'main_card_image' => '/images/bike-promo.jpg',
                'main_card_heading' => 'Elevate Your',
                'main_card_subheading' => 'Fitness Journey',
                'main_card_badge' => 'Up to',
                'main_card_badge_text' => '40% Discounts',
                'main_card_cta_text' => 'Shop Now',
                'main_card_cta_link' => '/products',
                
                // Top right card (Perfect Gear)
                'top_right_image' => '/images/gear-promo.jpg',
                'top_right_title' => 'Perfect Gear Awaits',
                'top_right_cta_text' => 'Shop Now',
                'top_right_cta_link' => '/products/accessories',
                
                // Bottom left card (Weights)
                'bottom_left_image' => '/images/weights-promo.jpg',
                'bottom_left_title' => 'Shine Bright with Weights',
                'bottom_left_cta_text' => 'Shop Now',
                'bottom_left_cta_link' => '/products/weights',
                
                // Bottom right card (TOP PICKS)
                'bottom_right_image' => '/images/top-picks.jpg',
                'bottom_right_title' => 'TOP PICKS',
                'bottom_right_cta_text' => 'Shop Now',
                'bottom_right_cta_link' => '/products/featured',
            ],
            'settings' => [
                'main_card_bg_overlay' => true,
                'text_color' => '#ffffff',
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 2. Pre-Order Showcase
        PageSection::create([
            'page_id' => $landingPage->id,
            'section_type' => 'preorder_showcase',
            'title' => 'Pre-Order Section',
            'content' => [
                'section_heading' => 'Pre-Order Now & Save Big',
                'view_all_text' => 'View All Preorder Products',
                'view_all_link' => '/products/preorder',
                'products' => [
                    [
                        'image' => '/images/treadmill-preorder.jpg',
                        'title' => 'Nordictrack T Series 10 Treadmill',
                        'badge' => 'Save 30%',
                        'cta_text' => 'Preorder Now',
                        'cta_link' => '/products/t-series-10',
                        'has_carousel' => true,
                    ],
                    [
                        'image' => '/images/elliptical-preorder.jpg',
                        'title' => 'Step Climber XL',
                        'badge' => '',
                        'cta_text' => 'Preorder Now',
                        'cta_link' => '/products/step-climber-xl',
                        'has_carousel' => false,
                    ],
                    [
                        'image' => '/images/bike-preorder.jpg',
                        'title' => 'Commercial S22i Studio Cycle',
                        'badge' => '',
                        'cta_text' => 'Preorder Now',
                        'cta_link' => '/products/s22i',
                        'has_carousel' => false,
                    ],
                    [
                        'image' => '/images/rower-preorder.jpg',
                        'title' => 'RW900 Rower',
                        'badge' => '',
                        'cta_text' => 'Preorder Now',
                        'cta_link' => '/products/rw900',
                        'has_carousel' => false,
                    ],
                ],
            ],
            'settings' => [
                'background_color' => '#f8f9fa',
                'grid_columns' => 4,
            ],
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // 3. Category Cards (Two Column)
        PageSection::create([
            'page_id' => $landingPage->id,
            'section_type' => 'category_cards_two_column',
            'title' => 'Equipment Categories',
            'content' => [
                // Left card - Cardio
                'left_card_image' => '/images/cardio-equipment.jpg',
                'left_card_title' => 'Cardio Equipment\'s',
                'left_card_description' => 'Burn calories and boost endurance with our premium cardio machines',
                'left_card_badge' => 'Save all 45%',
                'left_card_cta_text' => 'Shop Now',
                'left_card_cta_link' => '/categories/cardio',
                
                // Right card - Free Weights
                'right_card_image' => '/images/free-weights.jpg',
                'right_card_title' => 'Free Weight Equipment\'s',
                'right_card_description' => 'Burn calories and boost endurance with our premium cardio machines',
                'right_card_badge' => 'Up to 30%',
                'right_card_cta_text' => 'Shop Now',
                'right_card_cta_link' => '/categories/weights',
            ],
            'settings' => [
                'card_height' => 'large',
                'gap' => 'medium',
            ],
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // ============================================
        // BRAND PAGE (NordicTrack Example)
        // ============================================
        $brandPage = Page::create([
            'title' => 'NordicTrack',
            'slug' => 'brands/nordictrack',
            'type' => 'brand',
            'meta_title' => 'NordicTrack - Premium Fitness Equipment',
            'meta_description' => 'Explore NordicTrack\'s complete line of treadmills, bikes, ellipticals, and rowers with iFIT technology',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // 1. Brand Full Width CTA Banner (HERO)
        PageSection::create([
            'page_id' => $brandPage->id,
            'section_type' => 'brand_full_width_cta',
            'title' => 'Hero Banner',
            'content' => [
                'brand_id' => 1,
                'background_image' => '/images/home-gym-banner.jpg',
                'heading' => 'Turn Your Home Into A Complete',
                'subheading' => 'Fitness Space',
                'subheading_color' => '#FFC107',
                'description' => 'NordicTrack is a leader in home fitness equipment, known for innovative treadmills, ellipticals, and exercise bikes with iFIT technology integration.',
                'cta_text' => 'Shop Nordictrack',
                'cta_link' => '/brands/nordictrack',
            ],
            'settings' => [
                'height' => 'large',
                'text_position' => 'left',
                'text_color' => '#ffffff',
                'overlay_opacity' => 50,
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 2. Brand Category Grid
        PageSection::create([
            'page_id' => $brandPage->id,
            'section_type' => 'brand_category_grid',
            'title' => 'Equipment Categories',
            'content' => [
                'brand_id' => 1,
                'section_title' => 'Explore The Nordictrack Categories',
                'background_color' => '#2c2c2c',
                'text_color' => '#ffffff',
                'categories' => [
                    ['image' => '/images/bikes-category.jpg', 'title' => 'Bikes', 'link' => '/brands/nordictrack/bikes'],
                    ['image' => '/images/treadmills-category.jpg', 'title' => 'Treadmills', 'link' => '/brands/nordictrack/treadmills'],
                    ['image' => '/images/ellipticals-category.jpg', 'title' => 'Ellipticals', 'link' => '/brands/nordictrack/ellipticals'],
                    ['image' => '/images/rowers-category.jpg', 'title' => 'Rowers', 'link' => '/brands/nordictrack/rowers'],
                ],
            ],
            'settings' => [
                'columns' => 4,
                'card_style' => 'bordered',
            ],
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // 3. Brand Featured Products (Treadmill + Elliptical)
        PageSection::create([
            'page_id' => $brandPage->id,
            'section_type' => 'brand_featured_products',
            'title' => 'Featured Products',
            'content' => [
                'brand_id' => 1,
                
                // Left - Treadmill
                'left_product_image' => '/images/treadmill-featured.jpg',
                'left_product_title' => 'T Series 16 Treadmill',
                'left_product_badge' => '12 MPH speed',
                'left_product_cta_text' => 'Shop Treadmill',
                'left_product_cta_link' => '/brands/nordictrack/treadmills',
                
                // Right - Elliptical
                'right_product_image' => '/images/elliptical-featured.jpg',
                'right_product_title' => 'Step Climber XL',
                'right_product_badge' => '',
                'right_product_cta_text' => 'Shop Ellipticals',
                'right_product_cta_link' => '/brands/nordictrack/ellipticals',
            ],
            'settings' => [
                'card_height' => 'large',
                'gap' => 'medium',
            ],
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // 4. Brand Content with Images (Thinking Behind the Work)
        PageSection::create([
            'page_id' => $brandPage->id,
            'section_type' => 'brand_content_with_images',
            'title' => 'Brand Story',
            'content' => [
                'brand_id' => 1,
                'heading' => 'Thinking Behind the Work',
                'description' => 'NordicTrack delivers a premium personal training experience at home through expertly crafted fitness equipment and the innovative iFIT platform. With a strong legacy in cardio, NordicTrack designs industry-leading treadmills, bikes, ellipticals, and rowers that combine comfort, performance, and advanced technology. iFIT programs, led by top trainers, offer immersive workouts from iconic global locations while adapting to all fitness levels. By continuously innovating across strength, endurance, and cross-training, NordicTrack provides a complete, interactive home fitness solution—because you deserve the best.',
                'stats' => [
                    ['value' => '51 +', 'label' => 'Years of Experiences'],
                    ['value' => '1M +', 'label' => 'Happy Customers'],
                    ['value' => '50 +', 'label' => 'Available In Countries'],
                ],
                'images' => [
                    ['image' => '/images/brand-story-1.jpg', 'alt_text' => 'Person on bike'],
                    ['image' => '/images/brand-story-2.jpg', 'alt_text' => 'Person on rower'],
                    ['image' => '/images/brand-story-3.jpg', 'alt_text' => 'Training session'],
                ],
            ],
            'settings' => [
                'background_color' => '#ffffff',
                'image_layout' => 'collage',
            ],
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // 5. Brand Product Hero (Rowing Machine) - LAST
        PageSection::create([
            'page_id' => $brandPage->id,
            'section_type' => 'brand_product_hero',
            'title' => 'Product Showcase',
            'content' => [
                'brand_id' => 1, // NordicTrack
                'product_image' => '/images/rowing-machine.jpg',
                'heading' => 'Smart Rowing. Full-Body Results.',
                'subheading' => 'Real Progress.',
                'description' => 'Experience a powerful, low-impact workout that engages your entire body. Smart rowing machines automatically adjust resistance to match your trainer\'s intensity, helping you build strength, endurance, and consistency—every session, every stroke.',
                'cta_text' => 'Shop Rowers',
                'cta_link' => '/brands/nordictrack/rowers',
                'background_color' => '#e8f4f8',
            ],
            'settings' => [
                'layout' => 'image_left',
                'image_width' => '50%',
            ],
            'sort_order' => 5,
            'is_active' => true,
        ]);
    }
}
