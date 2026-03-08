<?php

namespace App\Services;

class PageTemplateService
{
    /**
     * Get all available section templates
     * These are predefined layouts that admins can use
     */
    public static function getSectionTemplates(): array
    {
        return [
            // LANDING PAGE TEMPLATES
            
            'landing_hero_grid' => [
                'name' => 'Landing Hero Grid (4 Sections)',
                'description' => 'Hero with 1 large card + 3 smaller cards OR video option',
                'category' => 'hero',
                'page_types' => ['landing'],
                'preview_image' => '/templates/landing-hero-grid.jpg',
                'schema' => [
                    'use_video' => ['type' => 'boolean', 'default' => false, 'label' => 'Use Video Instead of Grid'],
                    
                    // Video option
                    'video_url' => ['type' => 'text', 'required' => false, 'label' => 'Video URL (YouTube/Vimeo)'],
                    'video_poster' => ['type' => 'image', 'required' => false, 'label' => 'Video Poster Image'],
                    
                    // Main card (left side - large)
                    'main_card_image' => ['type' => 'image', 'required' => true],
                    'main_card_heading' => ['type' => 'text', 'required' => true, 'max' => 100],
                    'main_card_subheading' => ['type' => 'text', 'required' => false],
                    'main_card_badge' => ['type' => 'text', 'required' => false, 'label' => 'Badge (e.g., "Up to 40%")'],
                    'main_card_badge_text' => ['type' => 'text', 'required' => false, 'label' => 'Badge subtext (e.g., "Discounts")'],
                    'main_card_cta_text' => ['type' => 'text', 'required' => true],
                    'main_card_cta_link' => ['type' => 'text', 'required' => false],
                    
                    // Top right card
                    'top_right_image' => ['type' => 'image', 'required' => true],
                    'top_right_title' => ['type' => 'text', 'required' => true],
                    'top_right_cta_text' => ['type' => 'text', 'required' => true],
                    'top_right_cta_link' => ['type' => 'text', 'required' => false],
                    
                    // Bottom left card
                    'bottom_left_image' => ['type' => 'image', 'required' => true],
                    'bottom_left_title' => ['type' => 'text', 'required' => true],
                    'bottom_left_cta_text' => ['type' => 'text', 'required' => true],
                    'bottom_left_cta_link' => ['type' => 'text', 'required' => false],
                    
                    // Bottom right card (TOP PICKS)
                    'bottom_right_image' => ['type' => 'image', 'required' => true],
                    'bottom_right_title' => ['type' => 'text', 'required' => true],
                    'bottom_right_cta_text' => ['type' => 'text', 'required' => true],
                    'bottom_right_cta_link' => ['type' => 'text', 'required' => false],
                ],
                'settings_schema' => [
                    'main_card_bg_overlay' => ['type' => 'boolean', 'default' => true],
                    'text_color' => ['type' => 'color', 'default' => '#ffffff'],
                ]
            ],

            'preorder_showcase' => [
                'name' => 'Pre-Order Showcase Section',
                'description' => 'Section with heading and grid of pre-order products',
                'category' => 'products',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/preorder-showcase.jpg',
                'schema' => [
                    'section_heading' => ['type' => 'text', 'required' => true, 'default' => 'Pre-Order Now & Save Big'],
                    'view_all_text' => ['type' => 'text', 'required' => false, 'default' => 'View All Preorder Products'],
                    'view_all_link' => ['type' => 'text', 'required' => false],
                    
                    'products' => [
                        'type' => 'repeater',
                        'max' => 6,
                        'fields' => [
                            'image' => ['type' => 'image', 'required' => true],
                            'title' => ['type' => 'text', 'required' => true],
                            'badge' => ['type' => 'text', 'required' => false, 'label' => 'Badge (e.g., "Save 30%")'],
                            'cta_text' => ['type' => 'text', 'required' => true, 'default' => 'Preorder Now'],
                            'cta_link' => ['type' => 'text', 'required' => false],
                            'has_carousel' => ['type' => 'boolean', 'default' => false, 'label' => 'Show carousel dots'],
                        ]
                    ]
                ],
                'settings_schema' => [
                    'background_color' => ['type' => 'color', 'default' => '#f8f9fa'],
                    'grid_columns' => ['type' => 'select', 'options' => [2, 3, 4], 'default' => 3],
                ]
            ],

            'category_cards_two_column' => [
                'name' => 'Two Column Category Cards',
                'description' => 'Two side-by-side category cards with images, descriptions, and badges',
                'category' => 'content',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/category-cards-two.jpg',
                'schema' => [
                    // Left card
                    'left_card_image' => ['type' => 'image', 'required' => true],
                    'left_card_title' => ['type' => 'text', 'required' => true],
                    'left_card_description' => ['type' => 'textarea', 'required' => false],
                    'left_card_badge' => ['type' => 'text', 'required' => false, 'label' => 'Badge (e.g., "Save all 45%")'],
                    'left_card_cta_text' => ['type' => 'text', 'required' => true],
                    'left_card_cta_link' => ['type' => 'text', 'required' => false],
                    
                    // Right card
                    'right_card_image' => ['type' => 'image', 'required' => true],
                    'right_card_title' => ['type' => 'text', 'required' => true],
                    'right_card_description' => ['type' => 'textarea', 'required' => false],
                    'right_card_badge' => ['type' => 'text', 'required' => false, 'label' => 'Badge (e.g., "Up to 30%")'],
                    'right_card_cta_text' => ['type' => 'text', 'required' => true],
                    'right_card_cta_link' => ['type' => 'text', 'required' => false],
                ],
                'settings_schema' => [
                    'card_height' => ['type' => 'select', 'options' => ['medium', 'large'], 'default' => 'large'],
                    'gap' => ['type' => 'select', 'options' => ['small', 'medium', 'large'], 'default' => 'medium'],
                ]
            ],

            // BRAND PAGE TEMPLATES
            
            'brand_product_hero' => [
                'name' => 'Brand Product Hero',
                'description' => 'Side-by-side product showcase with image and content',
                'category' => 'hero',
                'page_types' => ['brand'],
                'preview_image' => '/templates/brand-product-hero.jpg',
                'schema' => [
                    'brand_id' => ['type' => 'brand_select', 'required' => false, 'label' => 'Link to Brand (optional)'],
                    'product_image' => ['type' => 'image', 'required' => true],
                    'heading' => ['type' => 'text', 'required' => true, 'max' => 100],
                    'subheading' => ['type' => 'text', 'required' => false, 'max' => 100],
                    'description' => ['type' => 'textarea', 'required' => true],
                    'cta_text' => ['type' => 'text', 'required' => true],
                    'cta_link' => ['type' => 'text', 'required' => false],
                    'background_color' => ['type' => 'color', 'default' => '#e8f4f8'],
                ],
                'settings_schema' => [
                    'layout' => ['type' => 'select', 'options' => ['image_left', 'image_right'], 'default' => 'image_left'],
                    'image_width' => ['type' => 'select', 'options' => ['40%', '50%', '60%'], 'default' => '50%'],
                ]
            ],

            'brand_content_with_images' => [
                'name' => 'Brand Content with Image Collage',
                'description' => 'Text content on left with multiple images on right',
                'category' => 'content',
                'page_types' => ['brand'],
                'preview_image' => '/templates/brand-content-images.jpg',
                'schema' => [
                    'brand_id' => ['type' => 'brand_select', 'required' => false],
                    'heading' => ['type' => 'text', 'required' => true],
                    'description' => ['type' => 'textarea', 'required' => true],
                    'stats' => [
                        'type' => 'repeater',
                        'max' => 3,
                        'fields' => [
                            'value' => ['type' => 'text', 'required' => true, 'label' => 'e.g., "51 +"'],
                            'label' => ['type' => 'text', 'required' => true, 'label' => 'e.g., "Years of Experiences"'],
                        ]
                    ],
                    'images' => [
                        'type' => 'repeater',
                        'max' => 4,
                        'fields' => [
                            'image' => ['type' => 'image', 'required' => true],
                            'alt_text' => ['type' => 'text', 'required' => false],
                        ]
                    ]
                ],
                'settings_schema' => [
                    'background_color' => ['type' => 'color', 'default' => '#ffffff'],
                    'image_layout' => ['type' => 'select', 'options' => ['collage', 'grid'], 'default' => 'collage'],
                ]
            ],

            'brand_featured_products' => [
                'name' => 'Brand Featured Products (Two Column)',
                'description' => 'Two large product cards side by side',
                'category' => 'products',
                'page_types' => ['brand'],
                'preview_image' => '/templates/brand-featured-products.jpg',
                'schema' => [
                    'brand_id' => ['type' => 'brand_select', 'required' => false],
                    
                    // Left product
                    'left_product_image' => ['type' => 'image', 'required' => true],
                    'left_product_title' => ['type' => 'text', 'required' => true],
                    'left_product_badge' => ['type' => 'text', 'required' => false, 'label' => 'Badge (e.g., "12 MPH speed")'],
                    'left_product_cta_text' => ['type' => 'text', 'required' => true, 'default' => 'Shop Treadmill'],
                    'left_product_cta_link' => ['type' => 'text', 'required' => false],
                    
                    // Right product
                    'right_product_image' => ['type' => 'image', 'required' => true],
                    'right_product_title' => ['type' => 'text', 'required' => true],
                    'right_product_badge' => ['type' => 'text', 'required' => false],
                    'right_product_cta_text' => ['type' => 'text', 'required' => true, 'default' => 'Shop Ellipticals'],
                    'right_product_cta_link' => ['type' => 'text', 'required' => false],
                ],
                'settings_schema' => [
                    'card_height' => ['type' => 'select', 'options' => ['medium', 'large'], 'default' => 'large'],
                    'gap' => ['type' => 'select', 'options' => ['small', 'medium', 'large'], 'default' => 'medium'],
                ]
            ],

            'brand_category_grid' => [
                'name' => 'Brand Category Grid',
                'description' => 'Grid of brand equipment categories',
                'category' => 'content',
                'page_types' => ['brand'],
                'preview_image' => '/templates/brand-category-grid.jpg',
                'schema' => [
                    'brand_id' => ['type' => 'brand_select', 'required' => false],
                    'section_title' => ['type' => 'text', 'required' => true, 'default' => 'Explore The Nordictrack Categories'],
                    'background_color' => ['type' => 'color', 'default' => '#2c2c2c'],
                    'text_color' => ['type' => 'color', 'default' => '#ffffff'],
                    'categories' => [
                        'type' => 'repeater',
                        'max' => 8,
                        'fields' => [
                            'image' => ['type' => 'image', 'required' => true],
                            'title' => ['type' => 'text', 'required' => true],
                            'link' => ['type' => 'text', 'required' => false],
                        ]
                    ]
                ],
                'settings_schema' => [
                    'columns' => ['type' => 'select', 'options' => [2, 3, 4], 'default' => 4],
                    'card_style' => ['type' => 'select', 'options' => ['bordered', 'shadow', 'flat'], 'default' => 'bordered'],
                ]
            ],

            'brand_full_width_cta' => [
                'name' => 'Brand Full Width CTA Banner',
                'description' => 'Full-width banner with background image and CTA',
                'category' => 'banner',
                'page_types' => ['brand'],
                'preview_image' => '/templates/brand-full-cta.jpg',
                'schema' => [
                    'brand_id' => ['type' => 'brand_select', 'required' => false],
                    'background_image' => ['type' => 'image', 'required' => true],
                    'heading' => ['type' => 'text', 'required' => true],
                    'subheading' => ['type' => 'text', 'required' => false],
                    'subheading_color' => ['type' => 'color', 'default' => '#FFC107', 'label' => 'Subheading Color (e.g., yellow for "Fitness Space")'],
                    'description' => ['type' => 'textarea', 'required' => false],
                    'cta_text' => ['type' => 'text', 'required' => true],
                    'cta_link' => ['type' => 'text', 'required' => false],
                ],
                'settings_schema' => [
                    'height' => ['type' => 'select', 'options' => ['medium', 'large', 'full'], 'default' => 'large'],
                    'text_position' => ['type' => 'select', 'options' => ['left', 'center', 'right'], 'default' => 'left'],
                    'text_color' => ['type' => 'color', 'default' => '#ffffff'],
                    'overlay_opacity' => ['type' => 'number', 'min' => 0, 'max' => 100, 'default' => 50],
                ]
            ],

            'brand_hero' => [
                'name' => 'Brand Hero Banner (Alternative)',
                'description' => 'Full-width hero banner for brand pages with logo',
                'category' => 'hero',
                'page_types' => ['brand'],
                'preview_image' => '/templates/brand-hero.jpg',
                'schema' => [
                    'brand_id' => ['type' => 'brand_select', 'required' => false, 'label' => 'Link to Brand (optional)'],
                    'background_image' => ['type' => 'image', 'required' => true],
                    'logo' => ['type' => 'image', 'required' => false, 'label' => 'Brand Logo'],
                    'heading' => ['type' => 'text', 'required' => true],
                    'subheading' => ['type' => 'text', 'required' => false],
                    'description' => ['type' => 'textarea', 'required' => false],
                    'cta_text' => ['type' => 'text', 'required' => false],
                    'cta_link' => ['type' => 'text', 'required' => false],
                ],
                'settings_schema' => [
                    'height' => ['type' => 'select', 'options' => ['medium', 'large', 'full'], 'default' => 'large'],
                    'text_position' => ['type' => 'select', 'options' => ['left', 'center', 'right'], 'default' => 'center'],
                    'text_color' => ['type' => 'color', 'default' => '#ffffff'],
                    'overlay_opacity' => ['type' => 'number', 'min' => 0, 'max' => 100, 'default' => 40],
                ]
            ],

            'promo_grid' => [
                'name' => 'Promotional Grid (3 Cards)',
                'description' => 'Grid layout with 1 large card and 2 smaller cards for promotions',
                'category' => 'content',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/promo-grid.jpg',
                'schema' => [
                    'main_card_image' => ['type' => 'image', 'required' => true],
                    'main_card_title' => ['type' => 'text', 'required' => true],
                    'main_card_subtitle' => ['type' => 'text', 'required' => false],
                    'main_card_badge' => ['type' => 'text', 'required' => false], // e.g., "Up to 40% Discounts"
                    'main_card_cta_text' => ['type' => 'text', 'required' => true],
                    'main_card_cta_link' => ['type' => 'text', 'required' => false],
                    
                    'side_card_1_image' => ['type' => 'image', 'required' => true],
                    'side_card_1_title' => ['type' => 'text', 'required' => true],
                    'side_card_1_cta_text' => ['type' => 'text', 'required' => true],
                    'side_card_1_cta_link' => ['type' => 'text', 'required' => false],
                    
                    'side_card_2_image' => ['type' => 'image', 'required' => true],
                    'side_card_2_title' => ['type' => 'text', 'required' => true],
                    'side_card_2_cta_text' => ['type' => 'text', 'required' => true],
                    'side_card_2_cta_link' => ['type' => 'text', 'required' => false],
                ],
                'settings_schema' => [
                    'layout' => ['type' => 'select', 'options' => ['main_left', 'main_right'], 'default' => 'main_left'],
                    'background_color' => ['type' => 'color', 'default' => '#ffffff'],
                ]
            ],

            'brand_products_grid' => [
                'name' => 'Brand Products Grid',
                'description' => 'Grid of products filtered by brand',
                'category' => 'products',
                'page_types' => ['brand'],
                'preview_image' => '/templates/brand-products.jpg',
                'schema' => [
                    'brand_id' => ['type' => 'brand_select', 'required' => false, 'label' => 'Filter by Brand'],
                    'section_title' => ['type' => 'text', 'required' => false],
                    'product_source' => ['type' => 'select', 'options' => ['manual', 'brand_featured', 'brand_all'], 'default' => 'brand_all'],
                    'limit' => ['type' => 'number', 'min' => 4, 'max' => 20, 'default' => 12],
                    
                    'products' => [
                        'type' => 'repeater',
                        'max' => 20,
                        'fields' => [
                            'product_id' => ['type' => 'product_select', 'required' => false],
                            'custom_image' => ['type' => 'image', 'required' => false],
                            'custom_title' => ['type' => 'text', 'required' => false],
                            'custom_link' => ['type' => 'text', 'required' => false],
                        ]
                    ]
                ],
                'settings_schema' => [
                    'columns' => ['type' => 'select', 'options' => [3, 4, 5], 'default' => 4],
                    'show_price' => ['type' => 'boolean', 'default' => true],
                    'show_add_to_cart' => ['type' => 'boolean', 'default' => true],
                ]
            ],

            'category_grid' => [
                'name' => 'Category Grid (4 Columns)',
                'description' => 'Grid of category cards with images',
                'category' => 'content',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/category-grid.jpg',
                'schema' => [
                    'section_title' => ['type' => 'text', 'required' => false],
                    'categories' => [
                        'type' => 'repeater',
                        'max' => 8,
                        'fields' => [
                            'image' => ['type' => 'image', 'required' => true],
                            'title' => ['type' => 'text', 'required' => true],
                            'link' => ['type' => 'text', 'required' => false],
                        ]
                    ]
                ],
                'settings_schema' => [
                    'columns' => ['type' => 'select', 'options' => [2, 3, 4], 'default' => 4],
                    'card_style' => ['type' => 'select', 'options' => ['bordered', 'shadow', 'flat'], 'default' => 'bordered'],
                ]
            ],

            'stats_section' => [
                'name' => 'Statistics Section',
                'description' => 'Display key metrics/stats (e.g., "51+ Years", "1M+ Customers")',
                'category' => 'content',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/stats-section.jpg',
                'schema' => [
                    'section_title' => ['type' => 'text', 'required' => false],
                    'section_description' => ['type' => 'textarea', 'required' => false],
                    'stats' => [
                        'type' => 'repeater',
                        'max' => 6,
                        'fields' => [
                            'value' => ['type' => 'text', 'required' => true], // e.g., "51 +"
                            'label' => ['type' => 'text', 'required' => true], // e.g., "Years of Experience"
                        ]
                    ]
                ],
                'settings_schema' => [
                    'layout' => ['type' => 'select', 'options' => ['horizontal', 'grid'], 'default' => 'horizontal'],
                    'background_color' => ['type' => 'color', 'default' => '#ffffff'],
                ]
            ],

            'text_with_images' => [
                'name' => 'Text Block with Side Images',
                'description' => 'Text content with collage of images on the side',
                'category' => 'content',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/text-with-images.jpg',
                'schema' => [
                    'heading' => ['type' => 'text', 'required' => true],
                    'description' => ['type' => 'textarea', 'required' => true],
                    'images' => [
                        'type' => 'repeater',
                        'max' => 4,
                        'fields' => [
                            'image' => ['type' => 'image', 'required' => true],
                            'alt_text' => ['type' => 'text', 'required' => false],
                        ]
                    ]
                ],
                'settings_schema' => [
                    'text_position' => ['type' => 'select', 'options' => ['left', 'right'], 'default' => 'left'],
                    'image_layout' => ['type' => 'select', 'options' => ['collage', 'grid', 'stack'], 'default' => 'collage'],
                ]
            ],

            'full_width_banner' => [
                'name' => 'Full Width Banner',
                'description' => 'Full-width promotional banner with text overlay',
                'category' => 'banner',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/full-width-banner.jpg',
                'schema' => [
                    'background_image' => ['type' => 'image', 'required' => true],
                    'heading' => ['type' => 'text', 'required' => true],
                    'subheading' => ['type' => 'text', 'required' => false],
                    'cta_text' => ['type' => 'text', 'required' => false],
                    'cta_link' => ['type' => 'text', 'required' => false],
                ],
                'settings_schema' => [
                    'height' => ['type' => 'select', 'options' => ['small', 'medium', 'large'], 'default' => 'medium'],
                    'text_alignment' => ['type' => 'select', 'options' => ['left', 'center', 'right'], 'default' => 'center'],
                    'text_color' => ['type' => 'color', 'default' => '#ffffff'],
                ]
            ],

            'product_showcase' => [
                'name' => 'Product Showcase Slider',
                'description' => 'Carousel/slider showcasing featured products',
                'category' => 'products',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/product-showcase.jpg',
                'schema' => [
                    'section_title' => ['type' => 'text', 'required' => false],
                    'products' => [
                        'type' => 'repeater',
                        'max' => 10,
                        'fields' => [
                            'product_id' => ['type' => 'product_select', 'required' => false],
                            'custom_image' => ['type' => 'image', 'required' => false],
                            'custom_title' => ['type' => 'text', 'required' => false],
                            'custom_link' => ['type' => 'text', 'required' => false],
                        ]
                    ]
                ],
                'settings_schema' => [
                    'autoplay' => ['type' => 'boolean', 'default' => true],
                    'slides_per_view' => ['type' => 'select', 'options' => [1, 2, 3, 4], 'default' => 4],
                ]
            ],

            'cta_section' => [
                'name' => 'Call-to-Action Section',
                'description' => 'Centered CTA with heading and button',
                'category' => 'cta',
                'page_types' => ['landing', 'brand'],
                'preview_image' => '/templates/cta-section.jpg',
                'schema' => [
                    'heading' => ['type' => 'text', 'required' => true],
                    'description' => ['type' => 'textarea', 'required' => false],
                    'cta_text' => ['type' => 'text', 'required' => true],
                    'cta_link' => ['type' => 'text', 'required' => false],
                ],
                'settings_schema' => [
                    'background_color' => ['type' => 'color', 'default' => '#f8f9fa'],
                    'button_style' => ['type' => 'select', 'options' => ['primary', 'secondary', 'outline'], 'default' => 'primary'],
                    'padding' => ['type' => 'select', 'options' => ['small', 'medium', 'large'], 'default' => 'large'],
                ]
            ],
        ];
    }

    /**
     * Get templates by category
     */
    public static function getTemplatesByCategory(string $category): array
    {
        $templates = self::getSectionTemplates();
        return array_filter($templates, fn($template) => $template['category'] === $category);
    }

    /**
     * Get templates by page type
     */
    public static function getTemplatesByPageType(string $pageType): array
    {
        $templates = self::getSectionTemplates();
        return array_filter($templates, function($template) use ($pageType) {
            return in_array($pageType, $template['page_types'] ?? []);
        });
    }

    /**
     * Get template schema for validation
     */
    public static function getTemplateSchema(string $templateType): ?array
    {
        $templates = self::getSectionTemplates();
        return $templates[$templateType] ?? null;
    }

    /**
     * Validate section content against template schema
     */
    public static function validateContent(string $templateType, array $content): array
    {
        $template = self::getTemplateSchema($templateType);
        if (!$template) {
            return ['valid' => false, 'errors' => ['Invalid template type']];
        }

        $errors = [];
        foreach ($template['schema'] as $field => $rules) {
            if ($rules['required'] ?? false) {
                if (!isset($content[$field]) || empty($content[$field])) {
                    $errors[] = "Field '{$field}' is required";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
