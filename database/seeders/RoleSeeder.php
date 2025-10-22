<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Quản lý',
            'description' => 'Có quyền thao tác tất cả các chức năng'
        ]);

        $itRole = Role::create([
            'name' => 'it',
            'display_name' => 'IT',
            'description' => 'Có quyền thao tác tất cả các chức năng'
        ]);

        $seoerRole = Role::create([
            'name' => 'seoer',
            'display_name' => 'Seoer',
            'description' => 'Chỉ có quyền xem ngân sách và tạo đề xuất'
        ]);

        $partnerRole = Role::create([
            'name' => 'partner',
            'display_name' => 'Đối tác',
            'description' => 'Có quyền xem đề xuất và cập nhật trạng thái đối tác'
        ]);

        $assistantRole = Role::create([
            'name' => 'assistant',
            'display_name' => 'Trợ lý, Tổ trưởng (TL)',
            'description' => 'Có quyền xem website, đề xuất và cập nhật trạng thái thanh toán'
        ]);

        // Assign permissions to roles
        $allPermissions = Permission::all();
        
        // Admin and IT have all permissions
        $adminRole->permissions()->attach($allPermissions);
        $itRole->permissions()->attach($allPermissions);

        // Seoer only has read permissions for budgets, services (approved only), and can create service proposals
        $seoerPermissions = Permission::whereIn('name', [
            'budgets.read',
            'services.read',
            'service_proposals.read',
            'service_proposals.create'
        ])->get();
        $seoerRole->permissions()->attach($seoerPermissions);

        // Partner can manage their own services and service proposals + withdrawal management
        $partnerPermissions = Permission::whereIn('name', [
            'services.create',
            'services.read',
            'services.update',
            'services.delete',
            'service_proposals.read',
            'service_proposals.update',
            'withdrawals.read',
            'withdrawals.create',
            'withdrawals.update',
            'withdrawals.delete'
        ])->get();
        $partnerRole->permissions()->attach($partnerPermissions);

        // Assistant (TL) can approve services, read websites, budgets, and update service proposals (payment status) + withdrawal processing
        $assistantPermissions = Permission::whereIn('name', [
            'websites.read',
            'budgets.read',
            'services.read',
            'services.approve',
            'service_proposals.read',
            'service_proposals.update',
            'withdrawals.read',
            'withdrawals.update'
        ])->get();
        $assistantRole->permissions()->attach($assistantPermissions);
    }
}
