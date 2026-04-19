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
        // Visitor Sessions Table
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); // OS
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('referrer')->nullable();
            $table->string('landing_page')->nullable();
            $table->timestamp('first_visit_at');
            $table->timestamp('last_activity_at');
            $table->integer('page_views')->default(0);
            $table->integer('duration_seconds')->default(0); // Total session duration
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('last_activity_at');
        });

        // Page Views Table
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('page_type'); // home, product, category, cart, checkout, etc.
            $table->string('page_url');
            $table->string('page_title')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            $table->index(['page_type', 'created_at']);
            $table->index(['product_id', 'created_at']);
            $table->index('viewed_at');
        });

        // Product Views Table (Aggregated)
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('view_count')->default(1);
            $table->integer('time_spent_seconds')->default(0);
            $table->boolean('added_to_cart')->default(false);
            $table->boolean('added_to_wishlist')->default(false);
            $table->boolean('purchased')->default(false);
            $table->timestamp('first_viewed_at');
            $table->timestamp('last_viewed_at');
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
            $table->index(['visitor_session_id', 'product_id']);
        });

        // Cart Events Table
        Schema::create('cart_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variation_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('event_type', ['added', 'updated', 'removed']);
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamp('event_at');
            $table->timestamps();
            
            $table->index(['visitor_session_id', 'event_type']);
            $table->index(['product_id', 'event_type']);
            $table->index('event_at');
        });

        // Checkout Funnel Table
        Schema::create('checkout_funnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'cart_viewed',
                'checkout_initiated',
                'shipping_info_entered',
                'payment_info_entered',
                'order_completed',
                'abandoned'
            ]);
            $table->json('cart_items')->nullable(); // Snapshot of cart
            $table->decimal('cart_total', 10, 2)->nullable();
            $table->integer('items_count')->default(0);
            $table->string('abandonment_reason')->nullable();
            $table->timestamp('cart_viewed_at')->nullable();
            $table->timestamp('checkout_initiated_at')->nullable();
            $table->timestamp('shipping_entered_at')->nullable();
            $table->timestamp('payment_entered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['visitor_session_id', 'status']);
            $table->index('abandoned_at');
        });

        // Search Queries Table
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('query');
            $table->integer('results_count')->default(0);
            $table->boolean('clicked_result')->default(false);
            $table->foreignId('clicked_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->timestamp('searched_at');
            $table->timestamps();
            
            $table->index('query');
            $table->index('searched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_queries');
        Schema::dropIfExists('checkout_funnels');
        Schema::dropIfExists('cart_events');
        Schema::dropIfExists('product_views');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('visitor_sessions');
    }
};
