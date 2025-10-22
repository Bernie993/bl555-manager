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
        Schema::create('service_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('service_name'); // Tên dịch vụ
            $table->integer('quantity'); // Số lượng dịch vụ
            $table->string('supplier_name'); // Nhà cung cấp
            $table->string('supplier_phone')->nullable(); // SĐT NCC
            $table->string('supplier_telegram')->nullable(); // User Telegram NCC
            $table->decimal('amount', 15, 2); // Số tiền
            $table->enum('status', ['pending', 'approved', 'rejected', 'confirmed', 'completed'])->default('pending');
            // pending: Chờ duyệt, approved: Đã duyệt, rejected: Từ chối, confirmed: Xác nhận đơn hàng, completed: Hoàn thành thanh toán
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Người tạo
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Người duyệt
            $table->foreignId('budget_id')->nullable()->constrained('budgets')->onDelete('set null'); // Liên kết với ngân sách
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamp('approved_at')->nullable(); // Thời gian duyệt
            $table->timestamp('confirmed_at')->nullable(); // Thời gian xác nhận
            $table->timestamp('completed_at')->nullable(); // Thời gian hoàn thành
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_proposals');
    }
};
