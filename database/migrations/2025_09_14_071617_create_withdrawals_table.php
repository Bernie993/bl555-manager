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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id')->comment('ID của Partner tạo yêu cầu rút tiền');
            $table->decimal('amount', 15, 2)->comment('Số tiền yêu cầu rút');
            $table->text('note')->nullable()->comment('Ghi chú từ Partner');
            $table->enum('status', ['pending', 'assistant_completed', 'partner_confirmed'])->default('pending')->comment('Trạng thái: pending, assistant_completed, partner_confirmed');
            
            // Assistant processing fields
            $table->unsignedBigInteger('assistant_processed_by')->nullable()->comment('ID Assistant xử lý');
            $table->timestamp('assistant_processed_at')->nullable()->comment('Thời gian Assistant xử lý');
            $table->string('payment_proof_image', 500)->nullable()->comment('Ảnh bill chuyển khoản');
            $table->text('assistant_note')->nullable()->comment('Ghi chú từ Assistant');
            
            // Partner confirmation fields
            $table->unsignedBigInteger('partner_confirmed_by')->nullable()->comment('ID Partner xác nhận nhận tiền');
            $table->timestamp('partner_confirmed_at')->nullable()->comment('Thời gian Partner xác nhận');
            $table->text('partner_confirmation_note')->nullable()->comment('Ghi chú xác nhận từ Partner');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('partner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assistant_processed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('partner_confirmed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['partner_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
