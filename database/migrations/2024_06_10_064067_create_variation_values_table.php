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
        Schema::create('variation_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variation_id');
            $table->unsignedBigInteger('variation_option_id');
            $table->timestamps();

            $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('cascade');
            $table->foreign('variation_option_id')->references('id')->on('variation_options')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_values');
    }
};
