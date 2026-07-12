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
        Schema::create('tracking_pixels', function (Blueprint $table) {
            $table->id();
            // Which third-party tracking service this record configures.
            $table->enum('provider', [
                'facebook_pixel',
                'google_ads',
                'google_analytics',
                'gtm',
                'custom',
            ]);
            $table->string('name'); // Friendly label shown in the admin panel
            // The provider identifier: FB pixel id, GTM-XXXXXXX, AW-XXXXXXXXX, G-XXXXXXXXXX, etc.
            $table->string('pixel_id')->nullable();
            // Optional raw snippets for the "custom" provider or advanced overrides.
            $table->text('custom_head_script')->nullable();
            $table->text('custom_body_script')->nullable();
            // Where the custom script should be injected on the storefront.
            $table->enum('placement', ['head', 'body_top', 'body_bottom'])->default('head');
            $table->boolean('is_active')->default(false);
            $table->string('gtm_dashboard_url')->nullable(); // Quick link to the GTM/Ads dashboard
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['provider', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_pixels');
    }
};
