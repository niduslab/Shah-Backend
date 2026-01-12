<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductImagesSeeder extends Seeder
{
    public function run()
    {
        // Path to the product images folder
        $imageDirectory = public_path('assets/imgs/product');

        // Get all files from the directory
        $files = File::files($imageDirectory);

        // Get all products from the database
        $products = Product::all();

        foreach ($products as $product) {
            // Loop through all available image files
            foreach ($files as $file) {
                // Create a unique file name by appending a timestamp to the original name
                $imageName = time() . '_' . $file->getFilename();

                // Construct the public URL to the image
                $imageUrl = Storage::url('assets/imgs/product/' . $file->getFilename());

                // Insert the image record into the 'images' table
                $imageRecord = Image::create([
                    'image_name' => $imageUrl,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create a relationship in the 'product_images' table
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_id' => $imageRecord->id,
                ]);
            }
        }
    }
}
