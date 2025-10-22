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
        Schema::table('websites', function (Blueprint $table) {
            // Thêm trường trạng thái 301
            $table->boolean('has_301_redirect')->default(false)->after('status'); // Có Rule 301 không
            $table->string('redirect_to_domain')->nullable()->after('has_301_redirect'); // Domain đang redirect đến
            $table->string('cloudflare_zone_id')->nullable()->after('redirect_to_domain'); // Cloudflare Zone ID
            
            // Thay đổi trường seoer thành foreign key
            $table->foreignId('seoer_id')->nullable()->after('name')->constrained('users')->onDelete('set null');
        });
        
        // Migration data từ seoer string sang seoer_id sẽ được xử lý sau
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropForeign(['seoer_id']);
            $table->dropColumn(['has_301_redirect', 'redirect_to_domain', 'cloudflare_zone_id', 'seoer_id']);
        });
    }
};
