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
        Schema::create('redirects_301', function (Blueprint $table) {
            $table->id();
            $table->text('domain_list'); // Danh sách domain cần chuyển hướng
            $table->string('target_url'); // URL cần chuyển 301
            $table->boolean('include_www')->default(false); // Bao gồm www trong 301
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->json('cloudflare_rules')->nullable(); // Lưu thông tin rules từ Cloudflare
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirects_301');
    }
};