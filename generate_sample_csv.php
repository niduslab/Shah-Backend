<?php
/**
 * Sample CSV Generator for Product Import Testing
 * 
 * Usage: php generate_sample_csv.php [number_of_products] [output_file]
 * Example: php generate_sample_csv.php 100 sample_products.csv
 */

// Configuration
$numProducts = isset($argv[1]) ? (int)$argv[1] : 50;
$outputFile = isset($argv[2]) ? $argv[2] : 'sample_products.csv';

// Sample data
$categories = [1, 2, 3, 4, 5]; // Update with your actual category IDs
$brands = [1, 2, 3, 4]; // Update with your actual brand IDs
$statuses = ['active', 'inactive', 'draft'];
$weightUnits = ['kg', 'g', 'lb'];

$productNames = [
    'Treadmill', 'Exercise Bike', 'Rowing Machine', 'Elliptical Trainer',
    'Yoga Mat', 'Dumbbell Set', 'Kettlebell', 'Resistance Bands',
    'Pull-up Bar', 'Ab Roller', 'Jump Rope', 'Foam Roller',
    'Weight Bench', 'Power Rack', 'Barbell', 'Weight Plates',
    'Medicine Ball', 'Balance Ball', 'Ankle Weights', 'Wrist Weights'
];

$adjectives = ['Pro', 'Premium', 'Elite', 'Advanced', 'Basic', 'Standard', 'Deluxe', 'Ultimate'];
$models = ['2000', '3000', '5000', 'X1', 'X2', 'Plus', 'Max', 'Lite'];

$descriptions = [
    'High-quality fitness equipment designed for home and commercial use.',
    'Professional-grade product with advanced features and durability.',
    'Perfect for beginners and experienced athletes alike.',
    'Compact design that fits in any space without compromising performance.',
    'Built with premium materials for long-lasting performance.',
    'Ergonomic design ensures comfort during extended use.',
    'Easy to assemble and maintain with included instructions.',
    'Versatile equipment suitable for various workout routines.'
];

// CSV Headers
$headers = [
    'name', 'sku', 'category_id', 'brand_id', 'model_id', 'shipping_class_id',
    'short_description', 'description',
    'price', 'compare_price', 'cost_price',
    'quantity', 'low_stock_threshold',
    'weight', 'weight_unit', 'length', 'width', 'height',
    'shipping_type', 'shipping_cost', 'requires_shipping', 'separate_shipping', 'shipping_notes',
    'is_featured', 'is_trending', 'kinomap',
    'status',
    'meta_title', 'meta_description', 'meta_keywords',
    'is_preorder', 'preorder_release_date', 'preorder_limit', 'preorder_deposit_amount', 'preorder_deposit_type',
];

// Add image columns
for ($i = 1; $i <= 10; $i++) {
    $headers[] = "image_$i";
    $headers[] = "image_{$i}_alt";
}

// Add variation columns
for ($i = 1; $i <= 10; $i++) {
    $headers[] = "variation_{$i}_sku";
    $headers[] = "variation_{$i}_attributes";
    $headers[] = "variation_{$i}_price";
    $headers[] = "variation_{$i}_quantity";
}

// Open file for writing
$file = fopen($outputFile, 'w');
if (!$file) {
    die("Error: Could not create file $outputFile\n");
}

// Write headers
fputcsv($file, $headers);

// Generate products
echo "Generating $numProducts sample products...\n";

for ($i = 1; $i <= $numProducts; $i++) {
    $productName = $productNames[array_rand($productNames)];
    $adjective = $adjectives[array_rand($adjectives)];
    $model = $models[array_rand($models)];
    
    $fullName = "$adjective $productName $model";
    $sku = strtoupper(substr($productName, 0, 3)) . '-' . $model . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
    
    $price = rand(2999, 199999) / 100; // $29.99 to $1999.99
    $comparePrice = $price * 1.3; // 30% higher
    $costPrice = $price * 0.6; // 60% of price
    
    $quantity = rand(0, 200);
    $weight = rand(50, 10000) / 100; // 0.5kg to 100kg
    
    $row = [
        $fullName, // name
        $sku, // sku
        $categories[array_rand($categories)], // category_id
        $brands[array_rand($brands)], // brand_id
        '', // model_id
        '', // shipping_class_id
        substr($descriptions[array_rand($descriptions)], 0, 100), // short_description
        $descriptions[array_rand($descriptions)], // description
        number_format($price, 2, '.', ''), // price
        number_format($comparePrice, 2, '.', ''), // compare_price
        number_format($costPrice, 2, '.', ''), // cost_price
        $quantity, // quantity
        5, // low_stock_threshold
        number_format($weight, 2, '.', ''), // weight
        $weightUnits[array_rand($weightUnits)], // weight_unit
        rand(20, 200), // length
        rand(20, 150), // width
        rand(10, 100), // height
        'default', // shipping_type
        '', // shipping_cost
        1, // requires_shipping
        0, // separate_shipping
        '', // shipping_notes
        rand(0, 1), // is_featured
        rand(0, 1), // is_trending
        rand(0, 1), // kinomap
        $statuses[array_rand($statuses)], // status
        "$fullName - Buy Online", // meta_title
        "Buy $fullName at the best price. High quality fitness equipment.", // meta_description
        strtolower(str_replace(' ', ',', $fullName)) . ',fitness,equipment', // meta_keywords
        0, // is_preorder
        '', // preorder_release_date
        '', // preorder_limit
        '', // preorder_deposit_amount
        '', // preorder_deposit_type
    ];
    
    // Add images (2-3 images per product)
    $numImages = rand(2, 3);
    for ($j = 1; $j <= 10; $j++) {
        if ($j <= $numImages) {
            $row[] = "https://via.placeholder.com/800x600/0066cc/ffffff?text=" . urlencode($fullName);
            $row[] = "$fullName - Image $j";
        } else {
            $row[] = '';
            $row[] = '';
        }
    }
    
    // Add variations (0-2 variations per product)
    $hasVariations = rand(0, 2);
    $colors = ['Black', 'White', 'Red', 'Blue', 'Gray'];
    $sizes = ['Small', 'Medium', 'Large', 'XL'];
    
    for ($j = 1; $j <= 10; $j++) {
        if ($j <= $hasVariations) {
            $color = $colors[array_rand($colors)];
            $size = $sizes[array_rand($sizes)];
            $varPrice = $price + rand(-1000, 1000) / 100;
            $varQty = rand(0, 50);
            
            $row[] = "$sku-V$j"; // variation_sku
            $row[] = "color:$color|size:$size"; // variation_attributes
            $row[] = number_format($varPrice, 2, '.', ''); // variation_price
            $row[] = $varQty; // variation_quantity
        } else {
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
        }
    }
    
    fputcsv($file, $row);
    
    if ($i % 100 == 0) {
        echo "Generated $i products...\n";
    }
}

fclose($file);

echo "\n✅ Successfully generated $numProducts products in $outputFile\n";
echo "File size: " . number_format(filesize($outputFile) / 1024, 2) . " KB\n";
echo "\nYou can now upload this file using:\n";
echo "curl -X POST \"http://your-domain.com/api/admin/products/import/upload\" \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN\" \\\n";
echo "  -F \"file=@$outputFile\"\n";
