<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Website permissions
            [
                'name' => 'websites.create',
                'display_name' => 'Tạo Website',
                'module' => 'websites',
                'action' => 'create',
                'description' => 'Quyền tạo website mới'
            ],
            [
                'name' => 'websites.read',
                'display_name' => 'Xem Website',
                'module' => 'websites',
                'action' => 'read',
                'description' => 'Quyền xem danh sách website'
            ],
            [
                'name' => 'websites.update',
                'display_name' => 'Sửa Website',
                'module' => 'websites',
                'action' => 'update',
                'description' => 'Quyền cập nhật thông tin website'
            ],
            [
                'name' => 'websites.delete',
                'display_name' => 'Xóa Website',
                'module' => 'websites',
                'action' => 'delete',
                'description' => 'Quyền xóa website'
            ],

            // Budget permissions
            [
                'name' => 'budgets.create',
                'display_name' => 'Tạo Ngân sách',
                'module' => 'budgets',
                'action' => 'create',
                'description' => 'Quyền tạo ngân sách mới'
            ],
            [
                'name' => 'budgets.read',
                'display_name' => 'Xem Ngân sách',
                'module' => 'budgets',
                'action' => 'read',
                'description' => 'Quyền xem danh sách ngân sách'
            ],
            [
                'name' => 'budgets.update',
                'display_name' => 'Sửa Ngân sách',
                'module' => 'budgets',
                'action' => 'update',
                'description' => 'Quyền cập nhật thông tin ngân sách'
            ],
            [
                'name' => 'budgets.delete',
                'display_name' => 'Xóa Ngân sách',
                'module' => 'budgets',
                'action' => 'delete',
                'description' => 'Quyền xóa ngân sách'
            ],

            // User permissions
            [
                'name' => 'users.create',
                'display_name' => 'Tạo Người dùng',
                'module' => 'users',
                'action' => 'create',
                'description' => 'Quyền tạo người dùng mới'
            ],
            [
                'name' => 'users.read',
                'display_name' => 'Xem Người dùng',
                'module' => 'users',
                'action' => 'read',
                'description' => 'Quyền xem danh sách người dùng'
            ],
            [
                'name' => 'users.update',
                'display_name' => 'Sửa Người dùng',
                'module' => 'users',
                'action' => 'update',
                'description' => 'Quyền cập nhật thông tin người dùng'
            ],
            [
                'name' => 'users.delete',
                'display_name' => 'Xóa Người dùng',
                'module' => 'users',
                'action' => 'delete',
                'description' => 'Quyền xóa người dùng'
            ],

            // Service Proposal permissions
            [
                'name' => 'service_proposals.create',
                'display_name' => 'Tạo Đề xuất dịch vụ',
                'module' => 'service_proposals',
                'action' => 'create',
                'description' => 'Quyền tạo đề xuất dịch vụ mới'
            ],
            [
                'name' => 'service_proposals.read',
                'display_name' => 'Xem Đề xuất dịch vụ',
                'module' => 'service_proposals',
                'action' => 'read',
                'description' => 'Quyền xem danh sách đề xuất dịch vụ'
            ],
            [
                'name' => 'service_proposals.update',
                'display_name' => 'Sửa Đề xuất dịch vụ',
                'module' => 'service_proposals',
                'action' => 'update',
                'description' => 'Quyền cập nhật thông tin đề xuất dịch vụ'
            ],
            [
                'name' => 'service_proposals.delete',
                'display_name' => 'Xóa Đề xuất dịch vụ',
                'module' => 'service_proposals',
                'action' => 'delete',
                'description' => 'Quyền xóa đề xuất dịch vụ'
            ],

            // Audit Log permissions
            [
                'name' => 'audit_logs.read',
                'display_name' => 'Xem Lịch sử thao tác',
                'module' => 'audit_logs',
                'action' => 'read',
                'description' => 'Quyền xem lịch sử thao tác hệ thống'
            ],
            [
                'name' => 'audit_logs.debug',
                'display_name' => 'Debug Audit Logs',
                'module' => 'audit_logs',
                'action' => 'debug',
                'description' => 'Quyền xem thông tin debug của audit logs'
            ],

            // Withdrawal permissions
            [
                'name' => 'withdrawals.read',
                'display_name' => 'Xem Yêu cầu rút tiền',
                'module' => 'withdrawals',
                'action' => 'read',
                'description' => 'Quyền xem danh sách và chi tiết yêu cầu rút tiền'
            ],
            [
                'name' => 'withdrawals.create',
                'display_name' => 'Tạo Yêu cầu rút tiền',
                'module' => 'withdrawals',
                'action' => 'create',
                'description' => 'Quyền tạo yêu cầu rút tiền mới'
            ],
            [
                'name' => 'withdrawals.update',
                'display_name' => 'Cập nhật Yêu cầu rút tiền',
                'module' => 'withdrawals',
                'action' => 'update',
                'description' => 'Quyền xử lý và cập nhật yêu cầu rút tiền'
            ],
            [
                'name' => 'withdrawals.delete',
                'display_name' => 'Xóa Yêu cầu rút tiền',
                'module' => 'withdrawals',
                'action' => 'delete',
                'description' => 'Quyền xóa yêu cầu rút tiền'
            ],

            // 301 Redirects permissions (IT only)
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

            // Services permissions
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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
