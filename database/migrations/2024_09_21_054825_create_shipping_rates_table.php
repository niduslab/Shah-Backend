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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('shipping_class_id')->nullable();
            $table->enum('method', ['shah_sports_team', 'pathao_courier']);
            $table->string('country', 100)->default('Bangladesh');
            $table->string('delivery_time', 100)->nullable();
            $table->decimal('free_shipping_min_order', 10, 2)->default(0);
            $table->decimal('base_cost', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('shipping_class_id')->references('id')->on('shipping_classes')->onDelete('set null');
        });

        Schema::create('weight_cost_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipping_rate_id');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->enum('shipping_calculation_method', ['per_unit', 'rules'])->default('per_unit');
            $table->decimal('per_unit_cost', 10, 2)->nullable();
            $table->decimal('default_rule_cost', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('shipping_rate_id')->references('id')->on('shipping_rates')->onDelete('cascade');
        });

        Schema::create('weight_cost_rule_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weight_cost_rule_id');
            $table->decimal('weight', 10, 2);
            $table->decimal('cost', 10, 2);
            $table->timestamps();

            $table->foreign('weight_cost_rule_id')->references('id')->on('weight_cost_rules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_cost_rule_items');
        Schema::dropIfExists('weight_cost_rules');
        Schema::dropIfExists('shipping_rates');
    }
};
