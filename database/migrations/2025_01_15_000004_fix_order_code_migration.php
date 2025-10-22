<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceProposal;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column exists
        if (!Schema::hasColumn('service_proposals', 'order_code')) {
            // Add column if it doesn't exist
            Schema::table('service_proposals', function (Blueprint $table) {
                $table->string('order_code')->nullable()->after('id');
            });
        }

        // Update existing proposals with order codes
        $this->updateExistingProposals();

        // Add unique constraint if it doesn't exist
        if (!$this->uniqueConstraintExists()) {
            Schema::table('service_proposals', function (Blueprint $table) {
                $table->unique('order_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_proposals', function (Blueprint $table) {
            $table->dropColumn('order_code');
        });
    }

    /**
     * Check if unique constraint exists
     */
    private function uniqueConstraintExists(): bool
    {
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'service_proposals' 
            AND CONSTRAINT_TYPE = 'UNIQUE' 
            AND CONSTRAINT_NAME LIKE '%order_code%'
        ");
        
        return count($constraints) > 0;
    }

    /**
     * Update existing proposals with order codes
     */
    private function updateExistingProposals(): void
    {
        $proposals = ServiceProposal::whereNull('order_code')->orWhere('order_code', '')->orderBy('created_at')->get();
        
        foreach ($proposals as $proposal) {
            $user = User::find($proposal->created_by);
            if ($user) {
                // Generate order code based on creation date
                $createdDate = $proposal->created_at->format('dmY');
                $username = strtoupper($user->name);
                
                // Count proposals created on the same date by this user
                $sameDateCount = ServiceProposal::where('created_by', $user->id)
                    ->whereDate('created_at', $proposal->created_at->toDateString())
                    ->where('id', '<=', $proposal->id)
                    ->whereNotNull('order_code')
                    ->where('order_code', '!=', '')
                    ->count();
                
                $sequenceNumber = str_pad($sameDateCount + 1, 2, '0', STR_PAD_LEFT);
                $orderCode = "{$username}-{$createdDate}-{$sequenceNumber}";
                
                // Ensure uniqueness by checking if code already exists
                $counter = 1;
                $originalCode = $orderCode;
                while (ServiceProposal::where('order_code', $orderCode)->exists()) {
                    $orderCode = $originalCode . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
                    $counter++;
                }
                
                $proposal->update(['order_code' => $orderCode]);
            }
        }
    }
};
