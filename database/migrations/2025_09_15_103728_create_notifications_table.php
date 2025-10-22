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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // service_created, service_updated, proposal_created, proposal_status_changed
            $table->string('title'); // Tiêu đề thông báo
            $table->text('message'); // Nội dung thông báo
            $table->json('data')->nullable(); // Dữ liệu bổ sung (IDs, links, etc.)
            
            // Người gửi (có thể null cho system notifications)
            $table->foreignId('from_user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Người nhận
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade');
            
            // Trạng thái
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Liên kết đến đối tượng (service, proposal, etc.)
            $table->string('notifiable_type')->nullable(); // App\Models\Service, App\Models\ServiceProposal
            $table->unsignedBigInteger('notifiable_id')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['to_user_id', 'is_read']);
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};