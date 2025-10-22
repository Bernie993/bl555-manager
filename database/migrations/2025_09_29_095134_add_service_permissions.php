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
        $servicePermissions = [
            [
                'name' => 'services.create',
                'display_name' => 'Tạo Dịch vụ',
                'module' => 'services',
                'action' => 'create',
                'description' => 'Quyền tạo dịch vụ mới'
            ],
            [
                'name' => 'services.read',
                'display_name' => 'Xem Dịch vụ',
                'module' => 'services',
                'action' => 'read',
                'description' => 'Quyền xem danh sách dịch vụ'
            ],
            [
                'name' => 'services.update',
                'display_name' => 'Sửa Dịch vụ',
                'module' => 'services',
                'action' => 'update',
                'description' => 'Quyền cập nhật thông tin dịch vụ'
            ],
            [
                'name' => 'services.delete',
                'display_name' => 'Xóa Dịch vụ',
                'module' => 'services',
                'action' => 'delete',
                'description' => 'Quyền xóa dịch vụ'
            ],
            [
                'name' => 'services.approve',
                'display_name' => 'Duyệt Dịch vụ',
                'module' => 'services',
                'action' => 'approve',
                'description' => 'Quyền duyệt/từ chối dịch vụ'
            ],
        ];

        foreach ($servicePermissions as $permission) {
            \App\Models\Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Update role permissions
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $itRole = \App\Models\Role::where('name', 'it')->first();
        $seoerRole = \App\Models\Role::where('name', 'seoer')->first();
        $partnerRole = \App\Models\Role::where('name', 'partner')->first();
        $assistantRole = \App\Models\Role::where('name', 'assistant')->first();

        $allServicePermissions = \App\Models\Permission::whereIn('name', [
            'services.create', 'services.read', 'services.update', 'services.delete', 'services.approve'
        ])->get();

        // Admin and IT have all permissions
        if ($adminRole) $adminRole->permissions()->syncWithoutDetaching($allServicePermissions);
        if ($itRole) $itRole->permissions()->syncWithoutDetaching($allServicePermissions);

        // Seoer can read services
        if ($seoerRole) {
            $seoerServicePermissions = \App\Models\Permission::whereIn('name', ['services.read'])->get();
            $seoerRole->permissions()->syncWithoutDetaching($seoerServicePermissions);
        }

        // Partner can manage their own services
        if ($partnerRole) {
            $partnerServicePermissions = \App\Models\Permission::whereIn('name', [
                'services.create', 'services.read', 'services.update', 'services.delete'
            ])->get();
            $partnerRole->permissions()->syncWithoutDetaching($partnerServicePermissions);
        }

        // Assistant can approve services
        if ($assistantRole) {
            $assistantServicePermissions = \App\Models\Permission::whereIn('name', [
                'services.read', 'services.approve'
            ])->get();
            $assistantRole->permissions()->syncWithoutDetaching($assistantServicePermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove service permissions from roles
        $servicePermissions = \App\Models\Permission::whereIn('name', [
            'services.create', 'services.read', 'services.update', 'services.delete', 'services.approve'
        ])->get();

        foreach ($servicePermissions as $permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
};
