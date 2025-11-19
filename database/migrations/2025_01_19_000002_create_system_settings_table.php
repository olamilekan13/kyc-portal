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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, textarea, json
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'signup_fee_amount',
                'value' => '5000',
                'type' => 'number',
                'group' => 'payments',
                'description' => 'Compulsory non-refundable signup fee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'bank_name',
                'value' => 'Your Bank Name',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Bank name for transfer payments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'bank_account_number',
                'value' => '0000000000',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Bank account number for transfer payments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'bank_account_name',
                'value' => 'Company Account Name',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Bank account name for transfer payments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paystack_public_key',
                'value' => '',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Paystack public key for online payments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paystack_secret_key',
                'value' => '',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Paystack secret key for online payments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
