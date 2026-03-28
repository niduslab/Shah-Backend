<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 100)->comment('Unique identifier: home, nordictrack, proform, etc.');
            $table->enum('page_type', ['landing', 'brand'])->comment('Type of page');
            $table->string('section_name', 100)->comment('Section identifier: hero, pre-order, categories, etc.');
            $table->string('title')->comment('Section title');
            $table->integer('sort_order')->default(0)->comment('Order of sections (1, 2, 3, etc.)');
            
            // For brand pages - link to brands table
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('cascade');
            
            // Store all content as JSON (flexible structure)
            $table->json('content')->comment('Section content stored as JSON');
            
            // Meta information
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Status and tracking
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('page_key');
            $table->index('page_type');
            $table->index('section_name');
            $table->index(['page_key', 'sort_order']);
            $table->index(['page_type', 'is_active']);
            
            // Unique constraint: one section per page
            $table->unique(['page_key', 'section_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_contents');
    }
};
