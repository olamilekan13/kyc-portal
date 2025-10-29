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
        Schema::table('kyc_forms', function (Blueprint $table) {
            // Add is_default column - only one form can be default at a time
            $table->boolean('is_default')->default(false)->after('status');

            // Add index for faster default form lookup
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_forms', function (Blueprint $table) {
            $table->dropIndex(['is_default']);
            $table->dropColumn('is_default');
        });
    }
};
