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
        Schema::create('kyc_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_submission_id')->constrained('kyc_submissions')->onDelete('cascade');
            $table->enum('type', ['email', 'sms']);
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_notifications');
    }
};
