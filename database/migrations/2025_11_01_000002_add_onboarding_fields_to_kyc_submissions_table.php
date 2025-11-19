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
        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->string('onboarding_token')->unique()->nullable()->after('id');
            $table->enum('onboarding_status', ['pending', 'in_progress', 'completed'])->default('pending')->after('onboarding_token');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->dropColumn(['onboarding_token', 'onboarding_status', 'onboarding_completed_at']);
        });
    }
};
