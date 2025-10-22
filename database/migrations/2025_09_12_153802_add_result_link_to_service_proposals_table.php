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
            $table->string('result_link', 500)->nullable()->after('partner_completed_at')->comment('Link file kết quả từ Partner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_proposals', function (Blueprint $table) {
            $table->dropColumn('result_link');
        });
    }
};
