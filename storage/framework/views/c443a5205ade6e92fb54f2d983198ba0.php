<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Dashboard</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.btn-lg {
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
}

.quick-action-card {
    transition: transform 0.2s;
}

.quick-action-card:hover {
    transform: translateY(-5px);
}

.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Header -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </h1>
            <div class="d-flex gap-2">
                <?php if(auth()->user()->hasRole('partner')): ?>
                <a href="<?php echo e(route('services.index')); ?>" class="btn btn-primary">
                    <i class="fas fa-cogs me-2"></i>
                    Quản lý Dịch vụ
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if(auth()->user()->hasRole('seoer')): ?>
    <!-- Seoer Dashboard -->
    <div class="row">
        <!-- Số đơn đã xác nhận -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Số đơn đã xác nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($seoerStats['confirmed_orders'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Số đơn đã hoàn thành -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Số đơn đã hoàn thành
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($seoerStats['completed_orders'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Số đơn đang chờ thanh toán -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Số đơn đang chờ thanh toán
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($seoerStats['pending_payment_orders'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ngân sách -->
    <div class="row">
        <!-- Ngân sách đã tiêu -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-danger stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Ngân sách đã tiêu
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($seoerStats['budget_spent'] ?? 0)); ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ngân sách còn lại -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-info stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Ngân sách còn lại
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($seoerStats['budget_remaining'] ?? 0)); ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách website được phân công -->
    <?php if(isset($assignedWebsites) && $assignedWebsites->count() > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-globe me-2"></i>
                        Website được phân công
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên website</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày giao</th>
                                    <th>Ngày hết hạn</th>
                                    <th>Ghi chú</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $assignedWebsites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $website): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($website->name); ?></strong>
                                        <?php if($website->cloudflare_zone_id): ?>
                                            <span class="badge bg-info ms-2">CF</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo e($website->getStatusBadgeClass()); ?>">
                                            <?php echo e($website->getStatusDisplayName()); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($website->delivery_date ? $website->delivery_date->format('d/m/Y') : '-'); ?></td>
                                    <td><?php echo e($website->expiry_date ? $website->expiry_date->format('d/m/Y') : '-'); ?></td>
                                    <td><?php echo e(Str::limit($website->notes ?? '-', 50)); ?></td>
                                    <td>
                                        <?php if(auth()->user()->canAccess('read', 'websites')): ?>
                                        <a href="<?php echo e(route('websites.show', $website)); ?>" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(auth()->user()->canAccess('read', 'websites')): ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('websites.index')); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>
                            Xem tất cả website
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Thao tác nhanh cho Seoer -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        Thao tác nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if(auth()->user()->canAccess('read', 'budgets')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo e(route('budgets.index')); ?>" class="btn btn-outline-primary btn-lg w-100 quick-action-card">
                                <i class="fas fa-wallet fa-2x mb-2"></i>
                                <br>Quản lý Ngân sách
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if(auth()->user()->canAccess('read', 'service_proposals')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo e(route('service-proposals.index')); ?>" class="btn btn-outline-success btn-lg w-100 quick-action-card">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <br>Đề xuất Dịch vụ
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if(auth()->user()->canAccess('read', 'services')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo e(route('services.index')); ?>" class="btn btn-outline-info btn-lg w-100 quick-action-card">
                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                <br>Dịch vụ có sẵn
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif(auth()->user()->hasRole('partner')): ?>
    <!-- Partner Dashboard -->
    <div class="row">
        <!-- Tổng Website -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Số dịch vụ đã đăng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($partnerStats['total_services'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Website hoạt động -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Số đề xuất
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($partnerStats['total_proposals'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tổng Ngân sách -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tổng tiền đã rút
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($partnerStats['total_withdrawn'] ?? 0)); ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Người dùng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tổng tiền chưa rút
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($partnerStats['total_pending'] ?? 0)); ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thao tác nhanh -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        Thao tác nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo e(route('services.index')); ?>" class="btn btn-outline-primary btn-lg w-100 quick-action-card">
                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                <br>Quản lý Dịch vụ
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo e(route('service-proposals.index')); ?>" class="btn btn-outline-success btn-lg w-100 quick-action-card">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <br>Đề xuất Dịch vụ
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo e(route('withdrawals.index')); ?>" class="btn btn-outline-info btn-lg w-100 quick-action-card">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                <br>Quản lý Rút tiền
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dịch vụ gần đây -->
    <?php if(isset($recentServices) && $recentServices->count() > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        Dịch vụ gần đây
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên dịch vụ</th>
                                    <th>Loại</th>
                                    <th>Website</th>
                                    <th>Giá</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($service->name); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo e($service->getTypeDisplayName()); ?></span>
                                    </td>
                                    <td>
                                        <?php if($service->website): ?>
                                            <a href="<?php echo e($service->website); ?>" target="_blank" class="text-primary">
                                                <?php echo e(parse_url($service->website, PHP_URL_HOST)); ?>

                                                <i class="fas fa-external-link-alt ms-1"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-success"><?php echo e($service->formatted_price); ?></strong>
                                    </td>
                                    <td>
                                        <?php if($service->is_active): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tạm dừng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($service->created_at->format('d/m/Y H:i')); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('services.index')); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>
                            Xem tất cả dịch vụ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Admin/IT/Seoer/Assistant Dashboard -->
    <div class="row">
        <!-- Tổng Website -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng Website
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['total_websites'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Website hoạt động -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Website hoạt động
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['active_websites'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tổng Ngân sách -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tổng Ngân sách
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['total_budgets'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Người dùng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Người dùng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['total_users'] ?? 0); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Website gần đây -->
    <?php if(isset($recentWebsites) && $recentWebsites->count() > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        Website gần đây
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên miền</th>
                                    <th>Trạng thái</th>
                                    <th>Seoer</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentWebsites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $website): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($website->domain); ?></strong>
                                        <?php if($website->cloudflare_zone_id): ?>
                                            <span class="badge bg-info ms-2">CF</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($website->status === 'active'): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo e(ucfirst($website->status)); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($website->seoer->name ?? '-'); ?></td>
                                    <td><?php echo e($website->created_at->format('d/m/Y H:i')); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('websites.show', $website)); ?>" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Thống kê ngân sách -->
    <?php if(isset($budgetSummary) && $budgetSummary): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>
                        Thống kê Ngân sách
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-primary"><?php echo e(number_format($budgetSummary->total_budget ?? 0)); ?> VNĐ</div>
                                <div class="text-muted">Tổng ngân sách</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-warning"><?php echo e(number_format($budgetSummary->total_spent ?? 0)); ?> VNĐ</div>
                                <div class="text-muted">Đã sử dụng</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-success"><?php echo e(number_format($budgetSummary->total_remaining ?? 0)); ?> VNĐ</div>
                                <div class="text-muted">Còn lại</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/dashboard/index.blade.php ENDPATH**/ ?>