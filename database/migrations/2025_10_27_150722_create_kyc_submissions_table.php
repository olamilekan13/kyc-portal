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
        Schema::create('kyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_form_id')->constrained('kyc_forms')->onDelete('cascade');
            $table->json('submission_data');
            $table->enum('status', ['pending', 'under_review', 'verified', 'approved', 'declined'])->default('pending');
            $table->enum('verification_status', ['not_verified', 'verified', 'failed'])->default('not_verified');
            $table->json('verification_response')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_submissions');
    }
};
