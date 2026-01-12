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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->longText('content');
            $table->enum('campaign_type', ['promotional', 'abandoned_cart', 'order_update', 'newsletter']);
            $table->enum('target_type', ['all_customers', 'specific_customers', 'customer_group'])->default('all_customers');
            $table->json('target_criteria')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'cancelled'])->default('draft');
            $table->integer('total_recipients')->default(0);
            $table->integer('total_sent')->default(0);
            $table->integer('total_opened')->default(0);
            $table->integer('total_clicked')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
