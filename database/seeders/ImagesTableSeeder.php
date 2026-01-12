<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use Illuminate\Support\Facades\File;

class ImageSeeder extends Seeder
{
    public function run()
    {
        $imageDirectory = public_path('assets/imgs/user_images');
        $files = File::files($imageDirectory);

        foreach ($files as $file) {
            Image::create([
                'image_name' => $file->getFilename(),
            ]);
        }
    }
}
