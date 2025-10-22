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
        Schema::table('services', function (Blueprint $table) {
            $table->string('quote_file')->nullable()->after('description'); // File báo giá
            $table->string('demo_file')->nullable()->after('quote_file'); // File demo
            $table->string('ref_domain')->nullable()->after('demo_file'); // Ref domain
            $table->string('traffic')->nullable()->after('ref_domain'); // Traffic
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['quote_file', 'demo_file', 'ref_domain', 'traffic']);
        });
    }
};
