<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the field_type enum to include 'nin'
        DB::statement("ALTER TABLE kyc_form_fields MODIFY COLUMN field_type ENUM('text', 'email', 'phone', 'file', 'date', 'select', 'textarea', 'number', 'nin') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'nin' from the enum
        DB::statement("ALTER TABLE kyc_form_fields MODIFY COLUMN field_type ENUM('text', 'email', 'phone', 'file', 'date', 'select', 'textarea', 'number') NOT NULL");
    }
};
