<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add redirects permissions
        $permissions = [
            [
                'name' => 'redirects.read',
                'display_name' => 'Xem Lệnh 301',
                'module' => 'redirects',
                'action' => 'read',
                'description' => 'Quyền xem danh sách và chi tiết lệnh 301'
            ],
            [
                'name' => 'redirects.create',
                'display_name' => 'Tạo Lệnh 301',
                'module' => 'redirects',
                'action' => 'create',
                'description' => 'Quyền tạo lệnh 301 mới'
            ],
            [
                'name' => 'redirects.update',
                'display_name' => 'Cập nhật Lệnh 301',
                'module' => 'redirects',
                'action' => 'update',
                'description' => 'Quyền cập nhật và kích hoạt/vô hiệu hóa lệnh 301'
            ],
            [
                'name' => 'redirects.delete',
                'display_name' => 'Xóa Lệnh 301',
                'module' => 'redirects',
                'action' => 'delete',
                'description' => 'Quyền xóa lệnh 301'
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Assign redirects permissions to IT role only (Admin gets all permissions by default)
        $itRole = Role::where('name', 'it')->first();
        if ($itRole) {
            $redirectsPermissions = Permission::where('module', 'redirects')->get();
            $itRole->permissions()->attach($redirectsPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove redirects permissions from roles
        $redirectsPermissions = Permission::where('module', 'redirects')->get();
        foreach ($redirectsPermissions as $permission) {
            $permission->roles()->detach();
        }

        // Delete redirects permissions
        Permission::where('module', 'redirects')->delete();
    }
};