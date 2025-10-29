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
        Schema::table('kyc_forms', function (Blueprint $table) {
            // Add slug column for user-friendly URLs
            $table->string('slug')->unique()->nullable()->after('name');

            // Add index for faster slug lookups
            $table->index('slug');
        });

        // Generate slugs for existing forms
        DB::statement("UPDATE kyc_forms SET slug = LOWER(REPLACE(REPLACE(REPLACE(name, ' ', '-'), '/', '-'), '&', 'and')) WHERE slug IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_forms', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};
