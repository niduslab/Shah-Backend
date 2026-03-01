<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_preorder')->default(false)->after('status');
            $table->dateTime('preorder_release_date')->nullable()->after('is_preorder');
            $table->integer('preorder_limit')->nullable()->after('preorder_release_date');
            $table->decimal('preorder_deposit_amount', 10, 2)->nullable()->after('preorder_limit');
            $table->enum('preorder_deposit_type', ['percentage', 'fixed'])->default('percentage')->after('preorder_deposit_amount');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_preorder',
                'preorder_release_date',
                'preorder_limit',
                'preorder_deposit_amount',
                'preorder_deposit_type'
            ]);
        });
    }
};
