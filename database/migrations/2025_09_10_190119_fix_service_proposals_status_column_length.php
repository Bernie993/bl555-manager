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
        Schema::table('service_proposals', function (Blueprint $table) {
            // Change status column to allow longer values
            $table->string('status', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_proposals', function (Blueprint $table) {
            // Revert to original length (if needed)
            $table->string('status', 20)->change();
        });
    }
};