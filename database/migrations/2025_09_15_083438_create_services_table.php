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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
            $table->string('name'); // Tên dịch vụ
            $table->enum('type', ['entity', 'backlink', 'textlink', 'guest_post']); // Loại dịch vụ
            $table->string('website'); // Website
            $table->integer('dr')->nullable(); // Domain Rating
            $table->integer('da')->nullable(); // Domain Authority
            $table->integer('pa')->nullable(); // Page Authority
            $table->integer('tf')->nullable(); // Trust Flow
            $table->string('ip')->nullable(); // IP Address
            $table->text('keywords')->nullable(); // Keywords (JSON or comma separated)
            $table->string('category')->nullable(); // Lĩnh vực
            $table->decimal('price', 15, 2); // Giá dịch vụ
            $table->text('description')->nullable(); // Mô tả dịch vụ
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->timestamps();

            // Indexes
            $table->index(['partner_id', 'type']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};