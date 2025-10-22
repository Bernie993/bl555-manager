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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên website
            $table->string('seoer'); // Seoer
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active'); // Trạng thái
            $table->date('delivery_date')->nullable(); // Ngày giao web
            $table->date('purchase_date')->nullable(); // Ngày mua web
            $table->date('expiry_date')->nullable(); // Ngày hết hạn
            $table->date('bot_open_date')->nullable(); // Ngày mở bot
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
