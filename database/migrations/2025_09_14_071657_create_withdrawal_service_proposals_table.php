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
        Schema::create('withdrawal_service_proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('withdrawal_id');
            $table->unsignedBigInteger('service_proposal_id');
            $table->decimal('amount', 15, 2)->comment('Số tiền rút từ đề xuất này');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('withdrawal_id')->references('id')->on('withdrawals')->onDelete('cascade');
            $table->foreign('service_proposal_id')->references('id')->on('service_proposals')->onDelete('cascade');
            
            // Unique constraint with custom name
            $table->unique(['withdrawal_id', 'service_proposal_id'], 'withdrawal_proposal_unique');
            
            // Indexes
            $table->index('withdrawal_id');
            $table->index('service_proposal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_service_proposals');
    }
};
