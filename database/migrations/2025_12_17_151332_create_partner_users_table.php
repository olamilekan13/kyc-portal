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
        Schema::create('partner_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_submission_id')->nullable()->constrained('kyc_submissions')->onDelete('cascade');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->boolean('kyc_form_completed')->default(false);
            $table->boolean('onboarding_form_completed')->default(false);
            $table->boolean('payment_completed')->default(false);
            $table->timestamp('last_accessed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('email');
            $table->index('kyc_submission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_users');
    }
};
