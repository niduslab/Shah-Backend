<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN shipping_method ENUM('shah_sports_team', 'pathao_courier', 'standard', 'none') NULL DEFAULT 'none'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN shipping_method ENUM('shah_sports_team', 'pathao_courier', 'none') NULL DEFAULT 'none'");
    }
};
