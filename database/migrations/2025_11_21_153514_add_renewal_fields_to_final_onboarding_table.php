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
            $table->date('partnership_start_date')->nullable()->after('payment_status');
            $table->date('partnership_end_date')->nullable()->after('partnership_start_date');
            $table->string('renewal_token', 64)->nullable()->unique()->after('partnership_end_date');
            $table->enum('renewal_status', ['active', 'pending_renewal', 'expired', 'renewed'])->default('active')->after('renewal_token');
            $table->timestamp('reminder_sent_at')->nullable()->after('renewal_status');
            $table->integer('reminder_count')->default(0)->after('reminder_sent_at');
            $table->integer('duration_months')->nullable()->after('reminder_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_onboarding', function (Blueprint $table) {
            $table->dropColumn([
                'partnership_start_date',
                'partnership_end_date',
                'renewal_token',
                'renewal_status',
                'reminder_sent_at',
                'reminder_count',
                'duration_months',
            ]);
        });
    }
};
