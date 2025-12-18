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
        Schema::create('partner_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_user_id')->constrained('partner_users')->onDelete('cascade');
            $table->foreignId('partnership_model_id')->nullable()->constrained('partnership_models')->onDelete('set null');
            $table->string('partnership_model_name');
            $table->decimal('partnership_model_price', 15, 2);
            $table->string('order_number')->unique(); // e.g., ORD-2025-00001

            // Solar Power
            $table->boolean('solar_power')->default(false);
            $table->decimal('solar_power_amount', 15, 2)->default(0);

            // Pricing
            $table->decimal('signup_fee_amount', 15, 2)->default(0); // 0 for additional orders
            $table->decimal('subtotal', 15, 2); // partnership + solar
            $table->decimal('total_amount', 15, 2); // subtotal + signup (if first order)

            // Payment Information
            $table->enum('payment_method', ['bank_transfer', 'paystack'])->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('payment_notes')->nullable();
            $table->string('payment_proof')->nullable();
            $table->json('paystack_response')->nullable();

            // Order Status
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_months')->nullable();

            // Additional Data
            $table->json('form_data')->nullable();
            $table->string('order_token')->unique(); // For accessing order without login

            $table->timestamps();

            $table->index('partner_user_id');
            $table->index('order_number');
            $table->index('order_token');
            $table->index('payment_status');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_orders');
    }
};
