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
        Schema::create('final_onboarding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_submission_id')->constrained('kyc_submissions')->onDelete('cascade');
            $table->foreignId('partnership_model_id')->nullable()->constrained('partnership_models')->onDelete('set null');
            $table->string('partnership_model_name')->nullable(); // Store name in case model is deleted
            $table->decimal('partnership_model_price', 15, 2)->nullable();
            $table->decimal('signup_fee_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);

            // Payment Information
            $table->enum('payment_method', ['bank_transfer', 'paystack'])->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'completed', 'failed'])->default('pending');

            // Signup Fee Payment
            $table->boolean('signup_fee_paid')->default(false);
            $table->string('signup_fee_reference')->nullable();
            $table->timestamp('signup_fee_paid_at')->nullable();

            // Partnership Model Fee Payment
            $table->boolean('model_fee_paid')->default(false);
            $table->string('model_fee_reference')->nullable();
            $table->timestamp('model_fee_paid_at')->nullable();

            // Additional tracking
            $table->text('payment_notes')->nullable();
            $table->json('paystack_response')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_onboarding');
    }
};
