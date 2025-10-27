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
        Schema::create('kyc_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_form_id')->constrained('kyc_forms')->onDelete('cascade');
            $table->enum('field_type', ['text', 'email', 'phone', 'file', 'date', 'select', 'textarea', 'number']);
            $table->string('field_name');
            $table->string('field_label');
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_form_fields');
    }
};
