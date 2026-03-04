<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Rename column from 'image' to 'image_path'
            $table->renameColumn('image', 'image_path');
        });

        // Add indexes for performance
        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'is_primary'], 'idx_product_primary');
            $table->index(['product_id', 'sort_order'], 'idx_product_sort');
        });

        // Set first image of each product as primary if no primary exists
        DB::statement("
            UPDATE product_images pi1
            SET is_primary = 1
            WHERE id = (
                SELECT MIN(id) 
                FROM (SELECT * FROM product_images) pi2 
                WHERE pi2.product_id = pi1.product_id
            )
            AND NOT EXISTS (
                SELECT 1 
                FROM (SELECT * FROM product_images) pi3 
                WHERE pi3.product_id = pi1.product_id 
                AND pi3.is_primary = 1
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('idx_product_primary');
            $table->dropIndex('idx_product_sort');
        });

        // Rename column back
        Schema::table('product_images', function (Blueprint $table) {
            $table->renameColumn('image_path', 'image');
        });
    }
};
