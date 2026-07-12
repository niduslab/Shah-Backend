<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('transaction_id');
            $table->string('proof_path')->nullable()->after('reference_number');
            $table->text('note')->nullable()->after('proof_path');
            $table->unsignedBigInteger('recorded_by')->nullable()->after('note');

            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->dropColumn(['reference_number', 'proof_path', 'note', 'recorded_by']);
        });
    }
};
