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
        Schema::create('final_onboarding_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('final_onboarding_form_id')->constrained('final_onboarding_forms')->onDelete('cascade');
            $table->enum('field_type', ['text', 'email', 'phone', 'date', 'file', 'select', 'textarea', 'number']);
            $table->string('field_name'); // e.g., 'business_name', 'company_address'
            $table->string('field_label'); // e.g., 'Business Name', 'Company Address'
            $table->json('validation_rules')->nullable(); // Custom validation rules
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable(); // For select fields
            $table->integer('order')->default(0); // Field ordering
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_onboarding_form_fields');
    }
};
