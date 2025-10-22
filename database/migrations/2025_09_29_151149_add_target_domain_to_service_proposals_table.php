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
            $table->string('target_domain')->nullable()->after('service_name')->comment('Domain/website mà dịch vụ sẽ được áp dụng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_proposals', function (Blueprint $table) {
            $table->dropColumn('target_domain');
        });
    }
};
