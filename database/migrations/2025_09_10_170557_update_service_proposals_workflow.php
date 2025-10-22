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
        Schema::table('service_proposals', function (Blueprint $table) {
            // Remove old field
            $table->dropColumn('supplier_phone');
            
            // Add new field
            $table->string('proposal_link')->nullable()->after('supplier_telegram');
            
            // Add new workflow fields
            $table->unsignedBigInteger('partner_confirmed_by')->nullable()->after('approved_by');
            $table->timestamp('partner_confirmed_at')->nullable()->after('approved_at');
            $table->unsignedBigInteger('partner_completed_by')->nullable()->after('partner_confirmed_by');
            $table->timestamp('partner_completed_at')->nullable()->after('partner_confirmed_at');
            $table->unsignedBigInteger('admin_completed_by')->nullable()->after('partner_completed_by');
            $table->timestamp('admin_completed_at')->nullable()->after('partner_completed_at');
            $table->unsignedBigInteger('payment_confirmed_by')->nullable()->after('admin_completed_by');
            $table->timestamp('payment_confirmed_at')->nullable()->after('admin_completed_at');
            
            // Add foreign keys
            $table->foreign('partner_confirmed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('partner_completed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('admin_completed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('payment_confirmed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_proposals', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['partner_confirmed_by']);
            $table->dropForeign(['partner_completed_by']);
            $table->dropForeign(['admin_completed_by']);
            $table->dropForeign(['payment_confirmed_by']);
            
            // Drop new columns
            $table->dropColumn([
                'proposal_link',
                'partner_confirmed_by',
                'partner_confirmed_at',
                'partner_completed_by',
                'partner_completed_at',
                'admin_completed_by',
                'admin_completed_at',
                'payment_confirmed_by',
                'payment_confirmed_at'
            ]);
            
            // Add back old field
            $table->string('supplier_phone')->nullable()->after('supplier_name');
        });
    }
};