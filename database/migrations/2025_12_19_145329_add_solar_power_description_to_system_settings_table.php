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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->text('solar_power_description')->nullable()->after('description');
        });

        // Delete the old solar_power_description record since we're moving to a dedicated column
        DB::table('system_settings')->where('key', 'solar_power_description')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn('solar_power_description');
        });
    }
};
