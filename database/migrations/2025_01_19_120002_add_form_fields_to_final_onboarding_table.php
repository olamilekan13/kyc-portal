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
            $table->foreignId('final_onboarding_form_id')->nullable()->after('kyc_submission_id')->constrained('final_onboarding_forms')->onDelete('set null');
            $table->json('form_data')->nullable()->after('final_onboarding_form_id'); // Store dynamic form field values
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_onboarding', function (Blueprint $table) {
            $table->dropForeign(['final_onboarding_form_id']);
            $table->dropColumn(['final_onboarding_form_id', 'form_data']);
        });
    }
};
