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
            $table->unsignedBigInteger('seoer_confirmed_by')->nullable()->after('partner_completed_at');
            $table->timestamp('seoer_confirmed_at')->nullable()->after('seoer_confirmed_by');
            
            $table->foreign('seoer_confirmed_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_proposals', function (Blueprint $table) {
            $table->dropForeign(['seoer_confirmed_by']);
            $table->dropColumn(['seoer_confirmed_by', 'seoer_confirmed_at']);
        });
    }
};
