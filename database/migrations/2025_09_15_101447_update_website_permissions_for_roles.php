<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove websites permissions from seoer role only
        // Admin, IT and Assistant (TL) should have access to websites
        
        $websitePermissions = Permission::where('module', 'websites')->get();
        $seoerRole = Role::where('name', 'seoer')->first();
        
        if ($seoerRole) {
            $seoerRole->permissions()->detach($websitePermissions->pluck('id'));
            echo "Removed website permissions from seoer role\n";
        }
        
        // Ensure assistant (TL) has websites.read permission
        $assistantRole = Role::where('name', 'assistant')->first();
        $websiteReadPermission = Permission::where('name', 'websites.read')->first();
        
        if ($assistantRole && $websiteReadPermission) {
            $assistantRole->permissions()->syncWithoutDetaching([$websiteReadPermission->id]);
            echo "Ensured assistant (TL) has websites.read permission\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore website read permissions to seoer only
        // Assistant (TL) should keep websites.read permission
        $websiteReadPermission = Permission::where('name', 'websites.read')->first();
        $seoerRole = Role::where('name', 'seoer')->first();
        
        if ($seoerRole && $websiteReadPermission) {
            $seoerRole->permissions()->attach($websiteReadPermission->id);
        }
    }
};