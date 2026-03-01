<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['product', 'banner', 'general'])->default('general');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gallery_id');
            $table->string('image_path');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('galleries');
    }
};
