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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_budget', 15, 2); // Ngân sách tổng (VNĐ)
            $table->string('seoer'); // Seoer
            $table->decimal('spent_amount', 15, 2)->default(0); // Số tiền đã tiêu
            $table->decimal('remaining_amount', 15, 2); // Số tiền còn lại
            $table->text('description')->nullable(); // Mô tả
            $table->date('period_start')->nullable(); // Ngày bắt đầu kỳ
            $table->date('period_end')->nullable(); // Ngày kết thúc kỳ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
