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
        Schema::table('final_onboarding', function (Blueprint $table) {
            $table->boolean('solar_power')->default(false)->after('total_amount');
            $table->decimal('solar_power_amount', 15, 2)->nullable()->after('solar_power');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_onboarding', function (Blueprint $table) {
            $table->dropColumn(['solar_power', 'solar_power_amount']);
        });
    }
};
