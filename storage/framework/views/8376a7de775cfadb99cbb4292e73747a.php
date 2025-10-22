<?php $__env->startSection('title', 'Quản lý Đề xuất Dịch vụ'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Quản lý Đề xuất Dịch vụ</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list me-2"></i>
                Quản lý Đề xuất Dịch vụ
            </h1>
            <div class="d-flex gap-2">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('withdrawals.read')): ?>
                <a href="<?php echo e(route('withdrawals.index')); ?>" class="btn btn-success">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Quản lý Rút tiền
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('services.index')); ?>" class="btn btn-primary">
                    <i class="fas fa-cogs me-2"></i>
                    Quản lý Dịch vụ
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('service-proposals.index')); ?>">
            <div class="row g-3">
                <!-- Service Filter -->
                <?php
                    $isPartner = auth()->user()->role && auth()->user()->role->name === 'partner';
                    $serviceColSize = $isPartner ? 'col-md-6' : 'col-md-3';
                ?>
                <div class="<?php echo e($serviceColSize); ?>">
                    <label for="service_id" class="form-label">Dịch vụ</label>
                    <select class="form-select" id="service_id" name="service_id">
                        <option value="">Tất cả dịch vụ</option>
                        <?php if($isPartner): ?>
                            <!-- Partner: Only their services -->
                            <?php $__currentLoopData = auth()->user()->services()->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($service->id); ?>" <?php echo e(request('service_id') == $service->id ? 'selected' : ''); ?>>
                                    <?php echo e($service->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <!-- Other roles: All services -->
                            <?php $__currentLoopData = \App\Models\Service::with('partner')->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($service->id); ?>" <?php echo e(request('service_id') == $service->id ? 'selected' : ''); ?>>
                                    <?php echo e($service->name); ?> (<?php echo e($service->partner->name ?? 'N/A'); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <!-- Partner Filter -->
                <?php if(!$isPartner): ?>
                <div class="col-md-3">
                    <label for="partner_id" class="form-label">Đối tác</label>
                    <select class="form-select" id="partner_id" name="partner_id">
                        <option value="">Tất cả đối tác</option>
                        <?php $__currentLoopData = \App\Models\User::whereHas('role', function($q) { $q->where('name', 'partner'); })->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($partner->id); ?>" <?php echo e(request('partner_id') == $partner->id ? 'selected' : ''); ?>>
                                <?php echo e($partner->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Domain Filter -->
                <?php if(!$isPartner): ?>
                <div class="col-md-2">
                    <label for="target_domain" class="form-label">Domain</label>
                    <?php if(auth()->user()->role->name === 'seoer'): ?>
                        <!-- Seoer: Dropdown with only their domains -->
                        <select class="form-select" id="target_domain" name="target_domain">
                            <option value="">Tất cả domain</option>
                            <?php $__currentLoopData = \App\Models\Website::where('seoer_id', auth()->id())->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $website): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($website->name); ?>" <?php echo e(request('target_domain') === $website->name ? 'selected' : ''); ?>>
                                    <?php echo e($website->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php else: ?>
                        <!-- Admin/IT/TL: Search input with suggestions -->
                        <input type="text" 
                               name="target_domain" 
                               id="target_domain" 
                               class="form-control" 
                               placeholder="Tìm domain..." 
                               value="<?php echo e(request('target_domain')); ?>"
                               autocomplete="off">
                        <div id="domain-suggestions" class="dropdown-menu" style="display: none; position: absolute; z-index: 1000; width: 100%;"></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Date Range Filter -->
                <div class="<?php echo e($isPartner ? 'col-md-3' : 'col-md-2'); ?>">
                    <label for="date_range" class="form-label">Thời gian</label>
                    <input type="text" name="date_range" id="date_range" class="form-control" 
                           placeholder="Chọn khoảng thời gian" 
                           value="<?php echo e(request('date_range')); ?>" readonly>
                    <input type="hidden" name="start_date" id="start_date" value="<?php echo e(request('start_date')); ?>">
                    <input type="hidden" name="end_date" id="end_date" value="<?php echo e(request('end_date')); ?>">
                </div>
                
                <!-- Status Filter -->
                <div class="<?php echo e($isPartner ? 'col-md-3' : 'col-md-2'); ?>">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Chờ duyệt</option>
                        <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Đã duyệt</option>
                        <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Từ chối</option>
                        <option value="partner_confirmed" <?php echo e(request('status') === 'partner_confirmed' ? 'selected' : ''); ?>>Đối tác xác nhận</option>
                        <option value="partner_completed" <?php echo e(request('status') === 'partner_completed' ? 'selected' : ''); ?>>Đối tác hoàn thành</option>
                        <option value="seoer_confirmed" <?php echo e(request('status') === 'seoer_confirmed' ? 'selected' : ''); ?>>Seoer xác nhận</option>
                        <option value="admin_completed" <?php echo e(request('status') === 'admin_completed' ? 'selected' : ''); ?>>Quản lý hoàn thành</option>
                        <option value="payment_confirmed" <?php echo e(request('status') === 'payment_confirmed' ? 'selected' : ''); ?>>Trợ lý xác nhận hoàn thành</option>
                    </select>
                </div>
            </div>
            
            <!-- Search and Filter Button Row -->
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo e(request('search')); ?>" placeholder="Tên dịch vụ hoặc nhà cung cấp...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>
                            Lọc
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <a href="<?php echo e(route('service-proposals.index')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Service Proposals Table -->
<div class="card">
    <div class="card-body">
        <?php if($proposals->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Dịch vụ</th>
                            <th>Nhà cung cấp</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Người tạo</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $proposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <strong><?php echo e($proposal->service_name); ?></strong>
                                <?php if($proposal->target_domain): ?>
                                    <br><small class="text-info">
                                        <i class="fas fa-globe me-1"></i><?php echo e($proposal->target_domain); ?>

                                    </small>
                                <?php endif; ?>
                                <br><small class="text-muted">SL: <?php echo e($proposal->quantity); ?></small>
                            </td>
                            <td>
                                <?php echo e($proposal->supplier_name); ?>

                                <?php if($proposal->proposal_link): ?>
                                    <br><a href="<?php echo e($proposal->proposal_link); ?>" target="_blank" class="text-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>Link đề xuất
                                    </a>
                                <?php endif; ?>
                                <?php if($proposal->result_link): ?>
                                    <br><a href="<?php echo e($proposal->result_link); ?>" target="_blank" class="text-success">
                                        <i class="fas fa-file-download me-1"></i>Link kết quả
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-primary"><?php echo e($proposal->formatted_amount); ?></strong>
                            </td>
                            <td>
                                <span class="badge <?php echo e($proposal->getStatusBadgeClass()); ?>">
                                    <?php echo e($proposal->getStatusDisplayName()); ?>

                                </span>
                                <?php
                                    $availableActions = $proposal->getAvailableActionsFor(auth()->user());
                                ?>
                                <?php if(count($availableActions) > 0): ?>
                                    <br><small class="text-info">Có thể thao tác</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo e($proposal->creator->name); ?>

                                <?php if($proposal->creator->id === auth()->id()): ?>
                                    <span class="badge bg-info ms-1">Bạn</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($proposal->created_at->format('d/m/Y H:i')); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?php echo e(route('service-proposals.show', $proposal)); ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php
                                        $userRole = auth()->user()->role->name ?? '';
                                        $canEdit = in_array($userRole, ['admin', 'it']) || 
                                                  ($proposal->created_by === auth()->id() && $proposal->status === 'pending');
                                    ?>
                                    <?php if($canEdit): ?>
                                    <a href="<?php echo e(route('service-proposals.edit', $proposal)); ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <!-- Dynamic Actions Based on Role and Status -->
                                    <?php
                                        $availableActions = $proposal->getAvailableActionsFor(auth()->user());
                                    ?>
                                    
                                    <?php $__currentLoopData = $availableActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $routeName = match($action['action']) {
                                                'approve' => 'service-proposals.approve',
                                                'reject' => 'service-proposals.reject',
                                                'partner_confirm' => 'service-proposals.partner-confirm',
                                                'partner_complete' => 'service-proposals.partner-complete',
                                                'seoer_confirm' => 'service-proposals.seoer-confirm',
                                                'admin_complete' => 'service-proposals.admin-complete',
                                                'payment_confirm' => 'service-proposals.payment-confirm',
                                                default => null
                                            };
                                            
                                            $icon = match($action['action']) {
                                                'approve' => 'fas fa-check',
                                                'reject' => 'fas fa-times',
                                                'partner_confirm' => 'fas fa-handshake',
                                                'partner_complete' => 'fas fa-check-circle',
                                                'seoer_confirm' => 'fas fa-user-check',
                                                'admin_complete' => 'fas fa-check-double',
                                                'payment_confirm' => 'fas fa-money-check-alt',
                                                default => 'fas fa-cog'
                                            };
                                        ?>
                                        
                                        <?php if($routeName): ?>
                                            <?php if($action['action'] === 'partner_complete'): ?>
                                                <!-- Special handling for partner complete - show modal -->
                                                <button type="button" class="btn btn-sm <?php echo e($action['class']); ?>" 
                                                        title="<?php echo e($action['label']); ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#partnerCompleteModal<?php echo e($proposal->id); ?>">
                                                    <i class="<?php echo e($icon); ?>"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- Regular form submission for other actions -->
                                                <form action="<?php echo e(route($routeName, $proposal)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PATCH'); ?>
                                                    <button type="submit" class="btn btn-sm <?php echo e($action['class']); ?>" title="<?php echo e($action['label']); ?>">
                                                        <i class="<?php echo e($icon); ?>"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    
                                    <?php
                                        $allowedDeleteStatuses = ['pending', 'approved'];
                                        $statusAllowsDelete = in_array($proposal->status, $allowedDeleteStatuses);
                                        
                                        $canDelete = $statusAllowsDelete && (
                                            in_array($userRole, ['admin', 'it']) || 
                                            ($proposal->created_by === auth()->id() && $proposal->status === 'pending')
                                        );
                                    ?>
                                    <?php if($canDelete): ?>
                                    <form action="<?php echo e(route('service-proposals.destroy', $proposal)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($proposals->withQueryString()->links()); ?>

            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Không có đề xuất dịch vụ nào</h5>
                <p class="text-muted">
                    <?php if(request()->hasAny(['search', 'status'])): ?>
                        Không tìm thấy đề xuất phù hợp với bộ lọc.
                    <?php else: ?>
                        Hãy tạo đề xuất dịch vụ đầu tiên của bạn.
                    <?php endif; ?>
                </p>
                <?php if(auth()->user()->hasPermission('service_proposals.create')): ?>
                <a href="<?php echo e(route('service-proposals.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Tạo Đề xuất
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Status Legend -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Quy trình Đề xuất Dịch vụ
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-warning me-2">Chờ duyệt</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-info me-2">Đã duyệt</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2">Đối tác xác nhận</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-dark me-2">Đối tác hoàn thành</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary me-2">Seoer xác nhận</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2">Quản lý hoàn thành</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success">Đã thanh toán</span>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <strong>Quy trình mới:</strong> Admin/IT duyệt → Partner xác nhận → Partner hoàn thành → <strong>Seoer xác nhận</strong> → Admin/IT xác nhận hoàn thành → Trợ lý xác nhận hoàn thành.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Partner Complete Modals -->
<?php $__currentLoopData = $proposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($proposal->canBePartnerCompletedBy(auth()->user())): ?>
    <div class="modal fade" id="partnerCompleteModal<?php echo e($proposal->id); ?>" tabindex="-1" aria-labelledby="partnerCompleteModalLabel<?php echo e($proposal->id); ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="partnerCompleteModalLabel<?php echo e($proposal->id); ?>">
                        <i class="fas fa-check-circle me-2"></i>
                        Xác nhận hoàn thành dịch vụ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo e(route('service-proposals.partner-complete', $proposal)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 class="fw-bold"><?php echo e($proposal->service_name); ?></h6>
                            <p class="text-muted mb-3">Nhà cung cấp: <?php echo e($proposal->supplier_name); ?></p>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Vui lòng cung cấp link file kết quả đã hoàn thành để Seoer có thể kiểm tra và xác nhận.
                        </div>
                        
                        <div class="mb-3">
                            <label for="result_link<?php echo e($proposal->id); ?>" class="form-label">
                                <i class="fas fa-link me-1"></i>
                                Link file kết quả <span class="text-danger">*</span>
                            </label>
                            <input type="url" 
                                   class="form-control" 
                                   id="result_link<?php echo e($proposal->id); ?>" 
                                   name="result_link" 
                                   placeholder="https://drive.google.com/... hoặc https://dropbox.com/..."
                                   required>
                            <div class="form-text">
                                Nhập link Google Drive, Dropbox, hoặc link khác chứa file kết quả đã hoàn thành
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Hủy
                        </button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-check-circle me-1"></i>
                            Xác nhận hoàn thành
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css" />
<style>
.daterangepicker {
    z-index: 9999 !important;
}
.daterangepicker .ranges li {
    color: #333;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 8px;
    cursor: pointer;
    padding: 8px 12px;
}
.daterangepicker .ranges li:hover {
    background-color: #e6e6e6;
    border-color: #adadad;
}
.daterangepicker .ranges li.active {
    background-color: #667eea;
    border-color: #667eea;
    color: white;
}
.daterangepicker .calendar-table {
    border: 1px solid #ddd;
    border-radius: 4px;
}
.daterangepicker td.active, .daterangepicker td.active:hover {
    background-color: #667eea;
    border-color: #667eea;
    color: #fff;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- Date Range Picker JS -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Initializing Service Proposals filters...');
    
    // Initialize date range picker
    setTimeout(function() {
        try {
            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Hủy',
                    applyLabel: 'Áp dụng',
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    customRangeLabel: 'Tùy chọn',
                    daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    monthNames: [
                        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
                    ],
                    firstDay: 1
                },
                ranges: {
                   'Hôm nay': [moment(), moment()],
                   'Hôm qua': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   '7 ngày qua': [moment().subtract(6, 'days'), moment()],
                   '30 ngày qua': [moment().subtract(29, 'days'), moment()],
                   'Tháng này': [moment().startOf('month'), moment().endOf('month')],
                   'Tháng trước': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                opens: 'left',
                drops: 'down',
                buttonClasses: 'btn btn-sm',
                applyClass: 'btn-primary',
                cancelClass: 'btn-secondary'
            });
            
            // Handle apply event
            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#start_date').val('');
                $('#end_date').val('');
            });
            
            // Set initial value if dates are provided
            <?php if(request('start_date') && request('end_date')): ?>
                $('#date_range').val('<?php echo e(\Carbon\Carbon::parse(request("start_date"))->format("d/m/Y")); ?>' + ' - ' + '<?php echo e(\Carbon\Carbon::parse(request("end_date"))->format("d/m/Y")); ?>');
            <?php endif; ?>
            
        } catch (error) {
            console.error('Error initializing date range picker:', error);
        }
    }, 500);

    // Auto-submit form when filters change  
    $('#service_id, #partner_id, #status').change(function() {
        $(this).closest('form').submit();
    });
    
    // Domain autocomplete functionality (reuse from budgets) - for Admin/IT/TL only
    let domainTimeout;
    let currentSuggestionIndex = -1;
    const domainSuggestionsUrl = '<?php echo e(url("/api/budgets/domain-suggestions")); ?>';
    
    $('#target_domain').on('input', function() {
        const query = $(this).val().trim();
        const suggestionsDiv = $('#domain-suggestions');
        
        clearTimeout(domainTimeout);
        
        if (query.length < 2) {
            suggestionsDiv.hide().empty();
            return;
        }
        
        domainTimeout = setTimeout(function() {
            $.ajax({
                url: domainSuggestionsUrl,
                method: 'GET',
                data: { q: query },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(domains) {
                    suggestionsDiv.empty();
                    currentSuggestionIndex = -1;
                    
                    if (domains.length > 0) {
                        domains.forEach(function(domain, index) {
                            const item = $('<button type="button" class="dropdown-item">' + domain + '</button>');
                            item.on('click', function() {
                                $('#target_domain').val(domain);
                                suggestionsDiv.hide();
                                $('#target_domain').closest('form').submit();
                            });
                            suggestionsDiv.append(item);
                        });
                        suggestionsDiv.show();
                    } else {
                        suggestionsDiv.hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching domain suggestions:', error);
                    suggestionsDiv.hide();
                }
            });
        }, 300);
    });
    
    // Handle keyboard navigation for domain suggestions
    $('#target_domain').on('keydown', function(e) {
        const suggestionsDiv = $('#domain-suggestions');
        const suggestions = suggestionsDiv.find('button');
        
        if (!suggestionsDiv.is(':visible') || suggestions.length === 0) {
            return;
        }
        
        switch(e.which) {
            case 40: // Arrow down
                e.preventDefault();
                currentSuggestionIndex = Math.min(currentSuggestionIndex + 1, suggestions.length - 1);
                updateSuggestionHighlight();
                break;
            case 38: // Arrow up
                e.preventDefault();
                currentSuggestionIndex = Math.max(currentSuggestionIndex - 1, -1);
                updateSuggestionHighlight();
                break;
            case 13: // Enter
                if (currentSuggestionIndex >= 0) {
                    e.preventDefault();
                    suggestions.eq(currentSuggestionIndex).click();
                }
                break;
            case 27: // Escape
                suggestionsDiv.hide();
                currentSuggestionIndex = -1;
                break;
        }
    });
    
    function updateSuggestionHighlight() {
        const suggestions = $('#domain-suggestions button');
        suggestions.removeClass('active');
        
        if (currentSuggestionIndex >= 0) {
            suggestions.eq(currentSuggestionIndex).addClass('active');
        }
    }
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#target_domain, #domain-suggestions').length) {
            $('#domain-suggestions').hide();
        }
    });
});
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/service-proposals/index.blade.php ENDPATH**/ ?>